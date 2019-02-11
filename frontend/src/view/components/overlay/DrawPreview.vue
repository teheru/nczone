<template>
  <nczone-overlay class="zone-draw-preview" :title="'NCZONE_DRAW_PREVIEW'" @close="cancel">
    <template slot="content">
      <div v-if="drawPossible">
        <span v-t="'NCZONE_DRAW_LOGGED_IN_PLAYERS'"></span>
        <ol class="zone-numeric-list">
          <li v-for="player in players" :key="player.id" v-html="player.username"></li>
        </ol>
        <span v-t="'NCZONE_DRAW_DO_YOU_WANT_TO_DRAW'"></span>
      </div>
      <div v-else="">
        <span v-t="'NCZONE_DRAW_PREVIEW_NOT_POSSIBLE'"></span>
      </div>
    </template>
    <template slot="actions">
      <button v-if="drawPossible" class="zone-button" v-t="'NCZONE_CANCEL'" @click="cancel"></button>
      <button v-if="drawPossible" class="zone-button" v-t="'NCZONE_OK'" @click="confirm"></button>
      <button v-if="!drawPossible" class="zone-button" v-t="'NCZONE_OK'" @click="cancel"></button>
    </template>
  </nczone-overlay>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-draw-preview',
  computed: {
    drawPossible () {
      return this.players.length > 0
    },
    players () {
      return this.overlayPayload.players
    },
    ...mapGetters([
      'overlayPayload'
    ])
  },
  methods: {
    cancel () {
      this.drawCancel()
    },
    confirm () {
      this.drawConfirm()
    },
    ...mapActions([
      'drawCancel',
      'drawConfirm'
    ])
  }
}
</script>
