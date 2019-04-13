<template>
  <nczone-overlay class="zone-map-description-overlay" title="NCZONE_MAP_DESCRIPTION_OVERLAY" @close="closeOverlay">
    <template slot="content">
      <nczone-map-description :map="overlayPayload.map" :viewTable="false"></nczone-map-description>
    </template>
  </nczone-overlay>
</template>
<script>
import { mapActions, mapGetters } from 'vuex'

export default {
  name: 'nczone-map-description-overlay',
  methods: {
    ...mapActions([
      'closeOverlay'
    ])
  },
  computed: {
    ...mapGetters([
      'overlayPayload'
    ])
  }
}
</script>
