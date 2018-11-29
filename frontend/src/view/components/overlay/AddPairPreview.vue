<template>
  <nczone-overlay class="zone-add-pair-preview" :title="'NCZONE_DRAW_PREVIEW'" @close="cancel">
    <template slot="content">
      <div v-if="canAddPair">
        <span v-t="'NCZONE_ADD_PLAYERS'"></span>
        <span class="zone-add-pair-player" v-html="player1.username"></span>,
        <span class="zone-add-pair-player" v-html="player2.username"></span>
      </div>
      <div v-else="">
        <span v-t="'NCZONE_ADD_PAIR_PREVIEW_NO_PLAYERS'"></span>
      </div>
    </template>
    <template slot="actions">
      <button v-if="canAddPair" class="zone-button" v-t="'NCZONE_CANCEL'" @click="cancel"></button>
      <button v-if="canAddPair" class="zone-button" v-t="'NCZONE_OK'" @click="confirm"></button>
      <button v-if="!canAddPair" class="zone-button" v-t="'NCZONE_OK'" @click="cancel"></button>
    </template>
  </nczone-overlay>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-add-pair-preview',
  computed: {
    matchId () {
      return this.overlayPayload.matchId
    },
    player1 () {
      return this.overlayPayload.player1
    },
    player2 () {
      return this.overlayPayload.player2
    },
    ...mapGetters([
      'overlayPayload',
      'canAddPair'
    ])
  },
  methods: {
    cancel () {
      this.addPairCancel()
    },
    confirm () {
      this.addPairConfirm(this.matchId)
    },
    ...mapActions([
      'addPairCancel',
      'addPairConfirm'
    ])
  }
}
</script>
