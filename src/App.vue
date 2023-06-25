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
							{{
							'Cancel link creation' }}
						</ActionButton>
						<ActionButton v-else
							icon="icon-delete"
							@click="deleteLink(link)">
							{{
							 'Delete link' }}
						</ActionButton>
					</template>
				</AppNavigationItem>
			</ul>
		</AppNavigation>
		<AppContent>
      <div id="filepicker">
        asdasdas
      </div>
		</AppContent>
	</div>
</template>

<script>
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/AppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/AppNavigationItem'
import AppNavigationNew from '@nextcloud/vue/dist/Components/AppNavigationNew'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'

export default {
	name: 'App',
	components: {
		ActionButton,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNew
	},
	data() {
		return {
			links: [],
			currentLinkId: null,
			updating: false,
			loading: true
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
			const response = await axios.get(generateUrl('/apps/linkcreator/links'))
			this.links = response.data
		} catch (e) {
			console.error(e)
			showError('Could not fetch links')
		}
		this.loading = false
	},

	methods: {
    onGetFilesPath(detail) {
      console.debug('files were selected')
      console.debug(detail.selection)
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
		padding: 20px;
		display: flex;
		flex-direction: column;
		flex-grow: 1;
	}

	input[type='text'] {
		width: 100%;
	}

	textarea {
		flex-grow: 1;
		width: 100%;
	}
</style>
