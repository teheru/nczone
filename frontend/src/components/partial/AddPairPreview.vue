<template>
  <div class="zone-add-pair-preview zone-overlay" v-if="addPairPreview.visible">
    <div class="zone-overlay-panel">
      <div class="zone-block">
        <div class="zone-title" v-t="'NCZONE_DRAW_PREVIEW'"></div>
        <div class="zone-content">
          <div v-if="canAddPair">
            <span v-t="'NCZONE_ADD_PLAYERS'"></span>
            <span class="zone-add-pair-player">{{player1.username}}</span>,
            <span class="zone-add-pair-player">{{player2.username}}</span>
          </div>
          <div v-else="">
            <span v-t="'NCZONE_ADD_PAIR_PREVIEW_NO_PLAYERS'"></span>
          </div>
        </div>
        <div v-if="canAddPair" class="zone-actions">
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
  name: 'nczone-add-pair-preview',
  computed: {
    ...mapGetters([
      'addPairPreview',
      'canAddPair'
    ]),
    player1 () {
      return this.addPairPreview.player1
    },
    player2 () {
      return this.addPairPreview.player2
    }
  },
  methods: {
    cancel () {
      this.$store.dispatch('addPairCancel')
    },
    confirm () {
      this.$store.dispatch('addPairConfirm')
    }
  }
}
</script>
