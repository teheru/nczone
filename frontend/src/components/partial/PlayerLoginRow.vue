<template>
  <div class="zone-player-login-row">
    <div class="zone-player-table-idx">{{ idx + 1 }}.</div>
    <div class="zone-player-table-username">{{ player.username }}</div>
    <div class="zone-player-table-rating">{{ player.rating }}</div>
  </div>
</template>
<script>
import {mapGetters} from 'vuex'
export default {
  name: 'nczone-player-login-row',
  props: {
    playerId: {
      type: Number,
      required: true
    },
    idx: {
      type: Number,
      required: true
    }
  },
  computed: {
    player () {
      return this.playerById(this.playerId)
    },
    ...mapGetters([
      'playerById'
    ])
  }
}
</script>
