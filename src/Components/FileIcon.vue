<template>
  <div class="nc-icon-container">
    <div v-if="node.haspreview" class="thumbnail" :style="`background-size: contain; height: ${size}; width: ${size}; background-image:url(/index.php/core/preview?fileId=${node.fileid});`"/>
  	<div v-else class="thumbnail" :style="`background-image:url(/index.php/apps/theming/img/core/filetypes/${getElemTypeClass(node)}); background-size: contain; height: ${size}; width: ${size}`"/>
  </div>
</template>

<script>
import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip.js';
import { getElemTypeClass } from '../js/utils.js';

export default {
  name: 'FileIcon',

  components: {},

  directives: {
    tooltip: Tooltip,
  },

  props: {
    node: {
      type: Object,
      required: true,
    },
    displayPreviews: {
      type: Boolean,
      default: true,
    },
	  size: {
		type: String,
		  default: '16px'
	  }
  },

	data() {
		return {
			getElemTypeClass,
		}
	}
}
</script>

<style scoped lang="scss">
.nc-icon-container {
  display: flex;
  align-items: center;
}
</style>
