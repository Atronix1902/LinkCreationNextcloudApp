import { basename } from '@nextcloud/paths';
import { getCurrentUser, getRequestToken } from "@nextcloud/auth";

const propertyRequestBody = `<?xml version="1.0"?>
<d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
  <d:prop>
        <d:getlastmodified />
        <d:getetag />
        <d:getcontenttype />
        <d:resourcetype />
        <oc:fileid />
        <oc:permissions />
        <oc:size />
        <d:getcontentlength />
        <nc:has-preview />
        <oc:favorite />
        <oc:comments-unread />
        <oc:owner-display-name />
        <oc:share-types />
  </d:prop>
</d:propfind>`

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

export default class WebdavFetchClient {
	path;
	basePath;
	hostName;
	protocol;
	baseUrl;
	url;
	breadcrumbs;
	loading;
	pathRegex;
	authHeader;
	triggers;

    constructor() {
        this.xmlParser = new DOMParser();
        this.ns = 'DAV:';

        const parsedUrl = new URL(window.location.href);
		this.path		= '';
        this.basePath	= parsedUrl.pathname;
		this.hostName	= parsedUrl.host;
		this.protocol	= parsedUrl.protocol;
		this.baseUrl	= `${this.protocol}//${this.hostName}`;
		this.url		= `${this.protocol}//${this.hostName}/remote.php/dav/files/${getCurrentUser().uid}/`;
		let dirs		= this.path.replace(/^\/|\/$/g, '').split('/');
		let breadcrumbs	= dirs.slice(0, 3).concat(['...'].concat(dirs.slice(-3)));
		if(dirs.length === 1 && dirs[0] === '') {
			dirs = [];
		}
		this.breadcrumbs	= dirs.length > 7 ? breadcrumbs : dirs;
		this.loading		= true;

		// this is used to compute the relative path (without relying on the base path)
        this.pathRegex = new RegExp('.*' + escapeRegExp(`/remote.php/dav/files/${getCurrentUser()}`), 'g');
		this.authHeader = getRequestToken();
		this.triggers	= {};
    }

	on(event, callback) {
		if(!this.triggers[event]) {
			this.triggers[event] = [];
		}
		this.triggers[event].push(callback);
	}

	#triggerEvent(event, params) {
		if(this.triggers[event]) {
			this.triggers[event].forEach(fn => fn(params));
		}
	}

    appendAuthHeader(headers) {
        if (this.authHeader) {
            headers.append('Requesttoken', this.authHeader);
        }
    }

    getAuthHeader() {
        if (this.authHeader) {
            return { Requesttoken: this.authHeader };
        }
    }

	async checkIfFileExists(path) {
		const headers = new Headers();
		headers.append('Accept', 'text/plain');
		headers.append('Depth', '1');
		headers.append('Content-Type', 'application/xml');
		this.appendAuthHeader(headers);

		const response = await fetch(this.url + path, {
			method: 'PROPFIND',
			headers: headers,
			credentials: 'include',
			body: propertyRequestBody
		});

		if(response.status >= 400) {
			return false;
		}

		const text			= await response.text();
		const dom			= this.xmlParser.parseFromString(text, 'application/xml');
		const responseList	= dom.documentElement.getElementsByTagNameNS(this.ns, 'response');
		const base			= Array.from(responseList).find(e => e.getElementsByTagNameNS(this.ns, 'href').item(0).innerHTML.replace(/\/+$/g, '') === `/remote.php/dav/files/${getCurrentUser().uid}/${path}`);
		if(!base) {
			return false;
		}
		const propstat		= Array.from(base.getElementsByTagNameNS(this.ns, 'propstat')).find(e => e.getElementsByTagNameNS(this.ns, 'status').item(0).innerHTML.includes('200 OK'));
		if(!propstat) {
			return false;
		}
		const prop			= propstat.getElementsByTagNameNS(this.ns, 'prop').item(0);
		if(!prop) {
			return false;
		}
		const permissions	= prop.getElementsByTagName('oc:permissions').item(0).innerHTML;
		return new RegExp(/^(?=.*R.*)(?=.*G.*)(?=.*D.*)(?=.*NV.*)(?=.*CK.*).*$/).test(permissions);
	}

    parseWebDavFileListXML(xmlString, path) {
        const dom			= this.xmlParser.parseFromString(xmlString, 'application/xml');
        const responseList	= dom.documentElement.getElementsByTagNameNS(this.ns, 'response');
        const result		= {
            nodes: [],
        }

		if(this.path.replaceAll('/', '') !== '') {
			result.nodes.push({
				filename:	path.replace(/^\/+|\/+$/g, '').replace(/\/?[^/]*$/, ''),
				type:		'directory',
				size:		0,
				haspreview:	false,
				basename:	'..',
				lastmod:	null
			});
		}

        for (let i = 0; i < responseList.length; i++) {
            const node	= {};
            const e		= responseList.item(i);

			node.filename		= decodeURIComponent(e.getElementsByTagNameNS(this.ns, 'href').item(0).innerHTML);
            node.filename		= node.filename.replace(this.pathRegex, '').replace(/\/$/, '');
            node.fileid			= parseInt(e.getElementsByTagName('oc:fileid')?.item(0)?.innerHTML ?? 0);
			node.size			= +e.getElementsByTagNameNS(this.ns, 'getcontentlength')?.item(0)?.innerHTML;
			if (!node.size) {
				node.size		= +e.getElementsByTagName('oc:size')?.item(0)?.innerHTML;
			}

			node.haspreview 	= e.getElementsByTagName('nc:has-preview')?.item(0)?.innerHTML.toLowerCase() === 'true';
			node.basename		= basename(node.filename);
			node.lastmod		= e.getElementsByTagNameNS(this.ns, 'getlastmodified').item(0).innerHTML;
			node.userrelative	= node.filename.replace(`/remote.php/dav/files/${getCurrentUser().uid}/`, '');

			if (e.getElementsByTagNameNS(this.ns, 'resourcetype').item(0).getElementsByTagNameNS(this.ns, 'collection').length > 0) {
				console.log(
					'filename: '+node.filename.replace(`/remote.php/dav/files/${getCurrentUser().uid}`, '').replace(/^\/+|\/+$/g,''),
					'path: '+path.replace(/^\/+|\/+$/g, ''));

				node.type = 'directory';
                // skip current directory
                if (node.filename.replace(`/remote.php/dav/files/${getCurrentUser().uid}`, '').replace(/^\/+|\/+$/g, '') === path.replace(/^\/+|\/+$/g, '')) {
					this.active = node;
                    continue;
                }
            }
			else {
                node.type = 'file';
                node.mime = e.getElementsByTagNameNS(this.ns, 'getcontenttype').item(0).innerHTML;
                node.etag = e.getElementsByTagNameNS(this.ns, 'getetag').item(0).innerHTML;
            }

            result.nodes.push(node);
        }
		this.active.dirs = result;

        return result
    }

    async getDirectoryContents(path) {
		console.log(`trying to find files for: ${this.url+path}`);
		this.path		= path;
		this.loading	= true;

        const headers	= new Headers();
        headers.append('Accept', 'text/plain');
        headers.append('Depth', '1');
        headers.append('Content-Type', 'application/xml');
        this.appendAuthHeader(headers);

		const response = await fetch(this.url + path, {
			method:			'PROPFIND',
			headers:		headers,
			credentials:	'include',
			body:			propertyRequestBody
		});

		const text = await response.text();

		if(response.status >= 400) {
			throw new Error(`Request Failed with message: ${text}`);
		}

		const parsed		= this.parseWebDavFileListXML(text, path);
		const dirObjects	= [];
		const dirs			= this.path.replace(/^\/|\/$/g, '').split('/');
		if(dirs.length === 1 && dirs[0] === '') {
			dirs.pop();
		}

		for(let i = 0; i < dirs.length; i++) {
			const dir = dirs[i];
			dirObjects.push({
				name:	dir,
				link:	dirs.slice(0, i+1).join('/')
			});
		}

		let breadcrumbs		= [];
		if(dirObjects.length > 7) {
			breadcrumbs = dirObjects.slice(0, 3);
			breadcrumbs.push({
				name:	'...',
				link:	dirObjects[3].link
			});
			breadcrumbs.push(...dirObjects.slice(-3));
		} else {
			breadcrumbs = dirObjects;
		}
		console.log(breadcrumbs);
		this.breadcrumbs	= breadcrumbs;
		this.loading		= false;
		console.log(`Found files: `, parsed);
		this.#triggerEvent('files_loaded', parsed);

		return parsed;
    }

    async createDirectory(path) {
        const headers = new Headers();
        this.appendAuthHeader(headers);

		const response = await fetch(this.url + path, {
			method:			'MKOL',
			credentials:	'include',
			headers:		headers
		});

		const text = await response.text();

		if(response.status >= 400) {
			throw new Error(`Request failed with message: ${text}`);
		}

		return this;
    }
}
