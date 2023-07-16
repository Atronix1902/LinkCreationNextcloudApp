<template>
	<EmptyContent>
		<template v-slot:icon>
			<h3>
				<i class="fas fa-spinner fa-spin"></i>
			</h3>
		</template>
	</EmptyContent>
</template>

<script>
import EmptyContent from "@nextcloud/vue/dist/Components/EmptyContent";
import '@fortawesome/fontawesome-free/css/all.css';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap/dist/js/bootstrap';

export default {
	name:		"LoadingIcon",
	components: {
		EmptyContent
	}
}
</script>

<style scoped>

</style>
