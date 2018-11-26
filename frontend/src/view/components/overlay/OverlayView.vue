<template>
  <component v-if="overlayComponent" v-bind:is="overlayComponent"></component>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'nczone-overlay-view',
  computed: {
    ...mapGetters([
      'overlayComponent'
    ])
  }
}
</script>
