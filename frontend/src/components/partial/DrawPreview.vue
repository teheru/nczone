<template>
  <div class="nczone-draw-preview nczone-overlay" v-if="drawPreview.visible">
    <div class="nczone-overlay-panel">
      <div class="zone-block">
        <div class="zone-title" v-t="'NCZONE_DRAW_PREVIEW'"></div>
        <div class="zone-content">
          DRAW PREVIEW!!! {{drawPreview.players}}
          <span v-t="'NCZONE_DRAW_DO_YOU_WANT_TO_DRAW'"></span>
        </div>
        <div class="zone-actions">
          <div class="zone-button" v-t="'NCZONE_CANCEL'" @click="cancel"></div>
          <div class="zone-button" v-t="'NCZONE_OK'" @click="confirm"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'

export default {
  name: 'nczone-draw-preview',
  computed: {
    ...mapGetters([
      'drawPreview'
    ])
  },
  methods: {
    cancel () {
      this.$store.dispatch('drawCancel')
    },
    confirm () {
      this.$store.dispatch('drawConfirm')
    }
  }
}
</script>
