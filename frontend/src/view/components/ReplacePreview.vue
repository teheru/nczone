<template>
  <div class="zone-replace-preview zone-overlay" v-if="replacePreview.visible">
    <div class="zone-overlay-panel">
      <div class="zone-block">
        <div class="zone-title-bar">
          <div class="zone-title" v-t="'NCZONE_DRAW_PREVIEW'"></div>
          <div class="zone-close-button" @click="cancel">x</div>
        </div>
        <div class="zone-content">
          <div v-if="canReplace">
            <span v-t="'NCZONE_REPLACE_PLAYER'"></span>
            <span class="zone-replace-player">{{replacePlayer.username}}</span>
            <span v-t="'NCZONE_REPLACE_BY_PLAYER'"></span>
            <span class="zone-replace-player">{{replaceByPlayer.username}}</span>
            <span v-t="'NCZONE_REPLACE_DO_YOU_WANT_TO_REPLACE'"></span>
          </div>
          <div v-else="">
            <span v-t="'NCZONE_REPLACE_PREVIEW_NO_PLAYERS'"></span>
          </div>
        </div>
        <div v-if="canReplace" class="zone-actions">
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
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-replace-preview',
  computed: {
    ...mapGetters([
      'replacePreview',
      'canReplace'
    ]),
    replacePlayer () {
      return this.replacePreview.replacePlayer
    },
    replaceByPlayer () {
      return this.replacePreview.replaceByPlayer
    }
  },
  methods: {
    cancel () {
      this.replaceCancel()
    },
    confirm () {
      this.replaceConfirm({ userId: this.replacePlayer.id })
    },
    ...mapActions([
      'replaceCancel',
      'replaceConfirm'
    ])
  }
}
</script>
