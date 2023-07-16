<template>
    <!--
    SPDX-FileCopyrightText: Steven-Hendrik Kriebel <steven.hendrik.kriebel@gmail.com>
    SPDX-License-Identifier: AGPL-3.0-or-later
    -->
	<div id="content" class="app-linkcreator">
		<AppNavigation>
			<AppNavigationNew v-if="!loading"
				:text="'New link'"
				:disabled="false"
				button-id="new-linkcreator-button"
				button-class="icon-add"
				@click="newLink" />
			<ul>
				<AppNavigationItem v-for="link in links"
					:key="link.id"
					:title="link.from ? link.from : 'New link'"
					:class="{active: currentLinkId === link.id}"
					@click="openLink(link)">
					<template slot="actions">
						<ActionButton v-if="link.id === -1"
							icon="icon-close"
							@click="cancelNewLink(link)">
							{{ 'Cancel link creation' }}
						</ActionButton>
						<ActionButton v-else
							icon="icon-delete"
							@click="deleteLink(link)">
							{{ 'Delete link' }}
						</ActionButton>
					</template>
				</AppNavigationItem>
			</ul>
		</AppNavigation>
		<AppContent id="app-content">
			<div v-if="!loading" class="d-flex flex-column">
				<div class="d-flex" id="pickers">
					<div class="col">
						<FilePicker @created="setFromClient" @selected="(selected) => updateInputContent(selected.dataset.userrelative, 'from')"></FilePicker>
					</div>
					<div class="col col-sm-auto vr mx-2" style="padding: 1px 0">
					</div>
					<div class="col">
						<FilePicker @created="setToClient" @selected="(selected) => updateInputContent(selected.dataset.userrelative, 'to')"></FilePicker>
					</div>
				</div>
				<div class="flex-fill d-flex justify-content-center p-2">
					<div class="w-100 d-flex flex-row justify-content-evenly">
						<div class="flex-fill m-2" style="margin-right: -0.2em !important">
							<i v-if="this['file-exists-from'] === true" class="fa-regular fa-square-check text-success h3" v-tooltip="'File or Directory Found'" style="position: relative; top: 0.15em"></i>
							<i v-if="this['file-exists-from'] === false" class="fa-regular fa-square-minus text-danger h3" v-tooltip="'File or Directory Found'" style="position: relative; top: 0.15em"></i>
							<i v-if="this['file-exists-from'] === null" class="fa-regular fa-square h3" style="position: relative; top: 0.15em"></i>
							<input type="text" id="from-input" @change="(event) => checkInputContent('from', event.target.value)" style="margin: -2.3em; padding-left: 2.3em">
						</div>
						<h1 v-if="!checkingInput" class="m-2" style="cursor: pointer;" v-tooltip="'Create Link'">
							<i v-if="this['file-exists-from'] === true && this['file-exists-to'] === false" class="fa-solid fa-arrow-right-long align-top text-success"></i>
							<i v-else-if="this['file-exists-from'] === false || this['file-exists-to'] === true" class="fa-solid fa-arrow-right-long align-top text-danger"></i>
							<i v-else-if="this['file-exists-from'] === null || this['file-exists-to'] === null" class="fa-solid fa-arrow-right-long align-top"></i>
						</h1>
						<LoadingIcon v-else class="m-0"></LoadingIcon>
						<div class="flex-fill m-2" style="margin-right: -0.2em !important">
							<i v-if="this['file-exists-to'] === true" class="fa-regular fa-square-minus text-danger h3" v-tooltip="'File or Directory Exists'" style="position: relative; top: 0.15em"></i>
							<i v-if="this['file-exists-to'] === false" class="fa-regular fa-square-check text-warning h3" v-tooltip="'File or Directory will be created'" style="position: relative; top: 0.15em"></i>
							<i v-if="this['file-exists-to'] === null" class="fa-regular fa-square h3" style="position: relative; top: 0.15em"></i>
							<input type="text" id="to-input" @change="(event) => checkInputContent('to', event.target.value)" style="margin: -2.3em; padding-left: 2.3em">
						</div>
					</div>
				</div>
			</div>
			<LoadingIcon v-else></LoadingIcon>
		</AppContent>
	</div>
</template>

<style>
.app-menu-main {
	margin-bottom: 0 !important;
	padding-left: 0 !important;
}
</style>

<script>
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton';
import AppContent from '@nextcloud/vue/dist/Components/AppContent';
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation';
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem';
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew';
import LoadingIcon from "./Components/LoadingIcon";
import VTooltip from 'v-tooltip';

import '@nextcloud/dialogs/styles/toast.scss';
import { generateUrl } from '@nextcloud/router';
import { showError, showSuccess } from '@nextcloud/dialogs';
import axios from '@nextcloud/axios';
import Vue from "vue";
import FilePicker from "./Components/FilePicker";
Vue.use(VTooltip);

export default {
	name:		'LinkCreator',
	components: {
		ActionButton,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew,
		LoadingIcon,
		FilePicker
	},
	data() {
		return {
			links:				this.links,
			url:				this.url,
			currentLinkId:		null,
			updating:			false,
			loading:			true,
			'file-exists-from':	null,
			'file-exists-to':	null,
			checkingInput:		false
		}
	},
	computed: {
		/**
		 * Return the currently selected link object
		 * @returns {Object|null}
		 */
		currentLink() {
			if (this.currentLinkId === null) {
				return null
			}
			return this.links.find((link) => link.id === this.currentLinkId)
		},

		/**
		 * Returns true if a link is selected and its title is not empty
		 * @returns {Boolean}
		 */
		savePossible() {
			return this.currentLink && this.currentLink.from !== ''
		},
	},
	/**
	 * Fetch list of links when the component is loaded
	 */
	async mounted() {
		try {
			const response		= await axios.get(generateUrl('/apps/linkcreator/links'));
			this.links			= response.data;
			Vue.prototype.OC	= window.OC;
			Vue.prototype.OCA	= window.OCA;
		} catch (e) {
			console.error(e);
			showError('Could not fetch links');
		}
		this.loading = false;
	},

	methods: {
		setFromClient(client) {
			this.fromClient = client;
		},
		setToClient(client) {
			this.toClient = client;
		},
		updateInputContent(value, type) {
			console.log("selected", value);
			document.querySelector(`#${type}-input`).value = value;
			this.checkInputContent(type, value);
		},
		 async checkInputContent(type, path) {
			console.log('triggered check');
			this.checkingInput = true;
			let exists = null;
			if(type === 'from') {
				this.existsFromPromise = this.fromClient.checkIfFileExists(path);
				exists = await this.existsFromPromise;
			}
			else {
				this.existsToPromise = this.toClient.checkIfFileExists(path);
				exists = await this.existsToPromise;
			}
			this.checkingInput = false;
			this[`file-exists-${type}`] = exists;
			console.log('exists:', exists);
			if(exists === true) {
				if(type === 'from') {
					document.querySelector(`#${type}-input`).classList.remove('border-danger');
					document.querySelector(`#${type}-input`).classList.add('border-success');
				}
				else {
					document.querySelector(`#${type}-input`).classList.remove('border-warning');
					document.querySelector(`#${type}-input`).classList.add('border-danger');
				}
			}
			else if(exists === false) {
				if(type === 'from') {
					document.querySelector(`#${type}-input`).classList.remove('border-success');
					document.querySelector(`#${type}-input`).classList.add('border-danger');
				}
				else {
					document.querySelector(`#${type}-input`).classList.remove('border-danger');
					document.querySelector(`#${type}-input`).classList.add('border-warning');
				}
			}
			else {
				document.querySelector(`#${type}-input`).classList.remove('border-success');
				document.querySelector(`#${type}-input`).classList.remove('border-danger');
			}
		},
		/**
		 * Create a new link and focus the link content field automatically
		 * @param {Object} link Link object
		 */
		openLink(link) {
			if (this.updating) {
				return
			}
			this.currentLinkId = link.id
			this.$nextTick(() => {
				this.$refs.content.focus()
			})
		},
		/**
		 * Action tiggered when clicking the save button
		 * create a new link or save
		 */
		saveLink() {
			if (this.currentLinkId === -1) {
				this.createLink(this.currentLink)
			} else {
				this.updateLink(this.currentLink)
			}
		},
		/**
		 * Create a new link and focus the link content field automatically
		 * The link is not yet saved, therefore an id of -1 is used until it
		 * has been persisted in the backend
		 */
		newLink() {
			if (this.currentLinkId !== -1) {
				this.currentLinkId = -1
				this.links.push({
					id: -1,
					from: '',
					to: '',
				})
				this.$nextTick(() => {
					this.$refs.from.focus()
				})
			}
		},
		/**
		 * Abort creating a new link
		 */
		cancelNewLink() {
			this.links.splice(this.links.findIndex((link) => link.id === -1), 1)
			this.currentLinkId = null
		},
		/**
		 * Create a new link by sending the information to the server
		 * @param {Object} link Link object
		 */
		async createLink(link) {
			this.updating = true
			try {
				const response = await axios.post(generateUrl('/apps/linkcreator/links'), link)
				const index = this.links.findIndex((match) => match.id === this.currentLinkId)
				this.$set(this.links, index, response.data)
				this.currentLinkId = response.data.id
			} catch (e) {
				console.error(e)
				showError('Could not create the link')
			}
			this.updating = false
		},
		/**
		 * Update an existing link on the server
		 * @param {Object} link Link object
		 */
		async updateLink(link) {
			this.updating = true
			try {
				await axios.put(generateUrl(`/apps/linkcreator/links/${link.id}`), link)
			} catch (e) {
				console.error(e)
				showError('Could not update the link')
			}
			this.updating = false
		},
		/**
		 * Delete a link, remove it from the frontend and show a hint
		 * @param {Object} link Link object
		 */
		async deleteLink(link) {
			try {
				await axios.delete(generateUrl(`/apps/linkcreator/links/${link.id}`))
				this.links.splice(this.links.indexOf(link), 1)
				if (this.currentLinkId === link.id) {
					this.currentLinkId = null
				}
				showSuccess('Link deleted')
			} catch (e) {
				console.error(e)
				showError('Could not delete the link')
			}
		},
	},
}
</script>
<style scoped>
	#app-content > div {
		width: 100%;
		height: 100%;
		padding: 3em;
		display: flex;
		flex-grow: 1;
		color: var(--color-main-text)
	}

	#app-navigation-vue {
		background-color: var(--color-main-background-blur, var(--color-main-background));
		-webkit-backdrop-filter: var(--filter-background-blur, none);
		backdrop-filter: var(--filter-background-blur, none);
	}

	#pickers {
		max-height: 75% !important;
	}

	input[type='text'] {
		width: 100%;
	}

	textarea {
		flex-grow: 1;
		width: 100%;
	}
</style>
