<template>
  <nczone-overlay class="zone-error-overlay" :title="'NCZONE_ERROR_OVERLAY'" @close="closeOverlay">
    <template slot="content">
      <div class="zone-error-overlay-message">
        <span v-t="overlayPayload.message"></span>
      </div>
    </template>
    <template slot="actions">
      <button class="zone-button" v-t="'NCZONE_OK'" @click="closeOverlay()"></button>
    </template>
  </nczone-overlay>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-error-overlay',
  computed: {
    ...mapGetters([
      'overlayPayload'
    ])
  },
  methods: {
    ...mapActions([
      'closeOverlay'
    ])
  }
}
</script>
