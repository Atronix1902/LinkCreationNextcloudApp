<template>
	<div v-if="!client.loading" class="flex-grow-1 overflow-scroll">
		<div v-for="file in files" @dblclick="doubleClickFolder(file)" @click="clickEntry">
			<FileEntry :file="file"></FileEntry>
			<hr style="margin: 1px 0">
		</div>
	</div>
	<LoadingIcon v-else></LoadingIcon>
</template>

<script>
import FileEntry from "./FileEntry";
import LoadingIcon from "./LoadingIcon";
import {getCurrentUser} from "@nextcloud/auth";

export default {
 	name: "FileList",
	components: {
		FileEntry,
		LoadingIcon
	},
	props: {
		client: {
			type:		Object,
			required:	true
		}
	},
	data() {
		return {
			files: this.files
		}
	},
	methods: {
		async doubleClickFolder(file) {
			console.log('dblclick');
			if(file.type === 'directory') {
				this.path	= `${file.filename.replace(`/remote.php/dav/files/${getCurrentUser().uid}`, '').replace(/^\/|\/$/g, '')}/`;
				await this.client.getDirectoryContents(this.path);
			}
		},
		clickEntry(event) {
			const parent = event.target.closest('.file-entry');
			if(parent.innerText === '..') {
				return;
			}
			this.setSelected(parent);
		},
		updateContent(parsed) {
			this.path	= this.client.path;
			this.files	= parsed.nodes;
		},
		setSelected(item) {
			if(this.client.selected) {
				this.client.selected.classList.remove('selected');
			}
			this.client.selected = item;
			this.client.selected.classList.add('selected');
			this.$emit('selected', this.client.selected);
		}
	},
	async mounted() {
		 this.client.on('files_loaded', params => this.updateContent(params));
		await this.client.getDirectoryContents('');
	}
}
</script>

<style scoped>

</style>
