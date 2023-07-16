<template>
	<div class="d-flex flex-column h-100">
		<div class="d-flex flex-row">
			<BreadCrumbs :client="client"></BreadCrumbs>
			<a class="button new" style="height: fit-content">
				<span class="icon icon-add"></span>
			</a>
		</div>
		<FileList :client="client" @selected="(selected) => this.$emit('selected', selected)"></FileList>
	</div>
</template>

<script>
import BreadCrumbs from "./BreadCrumbs";
import FileList from "./FileList";
import WebdavFetchClient from "../js/WebdavFetchClient";

export default {
	name: "FilePicker",
	components: {
		BreadCrumbs,
		FileList
	},
	data() {
		return {
			client: new WebdavFetchClient()
		}
	},
	mounted() {
		this.$emit('created', this.client);
	}
}
</script>

<style scoped>

</style>
