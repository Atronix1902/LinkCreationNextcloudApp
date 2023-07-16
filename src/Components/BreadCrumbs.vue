<template>
	<ul class="breadcrumb mb-3 overflow-y-scroll">
		<li class="crumb svg crumbhome" @click="goTo('')">
			<span class="icon-home">Home</span>
		</li>
		<li v-for="breadcrumb in client.breadcrumbs" @click="goTo(breadcrumb.link)" class="crumb">
			<span>{{ breadcrumb.name }}</span>
		</li>
	</ul>
</template>

<script>

export default {
	name: "BreadCrumbs",
	props: {
		client: {
			type:		Object,
			required:	true
		}
	},
	methods: {
		goTo(path) {
			this.client.getDirectoryContents(path);
		}
	}
}
</script>

<style scoped>

</style>
