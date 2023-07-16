<template>
	<div :class="`row py-2 ${file.type} file-entry w-100 m-0`" :data-name="file.basename" :data-filename="file.filename" :data-etag="file.etag" :data-fileid="file.fileid" :data-haspreview="file.haspreview" :data-lastmod="file.lastmod" :data-mime="file.mime" :data-size="file.size" :data-type="file.type" :data-userrelative="file.userrelative">
		<FileIcon :node="file" size="1.5em" class="col flex-grow-0" style="cursor: pointer"></FileIcon>
		<span class="col flex-grow-1 text-truncate" style="cursor: pointer">{{ file.basename }}</span>
		<span v-if="file.lastmod !== null" class="col col-sm-auto" style="cursor: pointer">{{ new Date(file.lastmod).toLocaleDateString('de-DE', {day: '2-digit', hour: "2-digit", minute: "2-digit", month: "short", year: "numeric"}) }}</span>
	</div>
</template>

<script>
import FileIcon from "./FileIcon";

export default {
	name: "FileEntry",
	components: {
		FileIcon
	},
	props: {
		file: {
			type:		Object,
			required:	true
		}
	}
}
</script>

<style scoped>
	.directory, .file {
		color: var(--color-main-text);
	}
	.file:hover, .directory:hover {
		background-color: var(--color-background-hover);
	}
	.directory.selected, .file.selected {
		background-color: var(--color-primary-element-light);
	}
</style>
