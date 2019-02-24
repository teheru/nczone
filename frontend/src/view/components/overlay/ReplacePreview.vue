<template>
  <nczone-overlay class="zone-replace-preview" :title="'NCZONE_PLAYER_DETAILS_TITLE'" @close="cancel">
    <template slot="content">
      <div v-if="canReplaceMod || canReplaceUser">
        <span v-t="'NCZONE_REPLACE_PLAYER'"></span>
        <span class="zone-replace-player" v-html="replacePlayer.username"></span>
        <span v-t="'NCZONE_REPLACE_BY_PLAYER'"></span>
        <span class="zone-replace-player" v-html="replaceByPlayer.username"></span>
        <span v-t="'NCZONE_REPLACE_DO_YOU_WANT_TO_REPLACE'"></span>
      </div>
      <div v-else="">
        <span v-t="'NCZONE_REPLACE_PREVIEW_NO_PLAYERS'"></span>
      </div>
    </template>
    <template slot="actions">
      <button v-if="canReplaceMod || canReplaceUser" class="zone-button" v-t="'NCZONE_CANCEL'" @click="cancel"></button>
      <button v-if="canReplaceMod || canReplaceUser" class="zone-button" v-t="'NCZONE_OK'" @click="confirm"></button>
      <button v-if="!canReplaceMod && !canReplaceUser" class="zone-button" v-t="'NCZONE_CANCEL'" @click="cancel"></button>
    </template>
  </nczone-overlay>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-replace-preview',
  computed: {
    replacePlayer () {
      return this.overlayPayload.replacePlayer
    },
    replaceByPlayer () {
      return this.overlayPayload.replaceByPlayer
    },
    ...mapGetters([
      'overlayPayload',
      'canReplaceMod',
      'canReplaceUser'
    ])
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
