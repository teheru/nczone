<template>
  <div class="zone-draw-preview zone-overlay" v-if="drawPreview.visible">
    <div class="zone-overlay-panel">
      <div class="zone-block">
        <div class="zone-title" v-t="'NCZONE_DRAW_PREVIEW'"></div>
        <div class="zone-content">
          <div v-if="drawPossible">
            <span v-t="'NCZONE_DRAW_LOGGED_IN_PLAYERS'"></span>
            <ol class="zone-numeric-list">
              <li v-for="(player, idx) in players" :key="idx">{{ player.username }}</li>
            </ol>
            <span v-t="'NCZONE_DRAW_DO_YOU_WANT_TO_DRAW'"></span>
          </div>
          <div v-else="">
            <span v-t="'NCZONE_DRAW_PREVIEW_NO_PLAYERS'"></span>
          </div>
        </div>
        <div v-if="drawPossible" class="zone-actions">
          <button class="zone-button" v-t="'NCZONE_CANCEL'" @click="cancel"></button>
          <button class="zone-button" v-t="'NCZONE_OK'" @click="confirm"></button>
        </div>
        <div v-else="" class="zone-actions">
          <button class="zone-button" v-t="'NCZONE_OK'" @click="cancel"></button>
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
    ]),
    drawPossible () {
      return this.players.length > 0
    },
    players () {
      return this.drawPreview.players
    }
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
