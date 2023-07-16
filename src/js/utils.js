/**
 * This file includes code derived from or inspired by the source file https://github.com/julien-nc/nextcloud-webdav-filepicker/blob/main/src/utils.js.
 *
 * https://github.com/julien-nc/nextcloud-webdav-filepicker/blob/main/src/utils.js is licensed under the GNU Affero General Public License (AGPL) v3.0.
 * For more details, see the LICENSE file.
 */

export function getElemTypeClass(elem) {
    if (elem.type === 'directory') {
        return 'folder.svg';
    } else {
        const mime = elem.mime;
        if (mime.match(/^video\//)) {
            return 'video.svg';
        }
		else if (mime === 'text/calendar') {
            return 'text-calendar.svg';
        }
		else if (mime === 'text/csv' || mime.match(/^application\/.*opendocument\.spreadsheet$/) || mime.match(/^application\/.*office.*sheet$/)) {
            return 'x-office-spreadsheet.svg';
        }
		else if (mime.match(/^text\//)) {
            return 'text.svg';
        }
		else if (mime.match(/^application\/pdf$/)) {
            return 'application-pdf.svg';
        }
		else if (mime.match(/^application\/gpx/)) {
            return 'location.svg';
        }
		else if (mime.match(/^image\//)) {
            return 'image.svg';
        }
		else if (mime.match(/^audio\//)) {
            return 'audio.svg';
        }
		else if (mime.match(/^application\/.*opendocument\.text$/) || mime.match(/^application\/.*word.*document$/)) {
            return 'x-office-document.svg';
        }
		else if (mime.match(/^application\/.*opendocument\.presentation$/) || mime.match(/^application\/.*office.*presentation$/)) {
            return 'x-office-presentation.svg';
        }
        return 'file.svg';
    }
}
