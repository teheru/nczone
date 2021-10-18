<template>
  <div v-if="canViewLogin" id="zone-player-area">
    <div class="zone-block">
      <div class="zone-title" v-t="'NCZONE_LOGGEDIN'"></div>
      <div v-if="canViewLogin" class="zone-content">
        <div v-if="players.length === 0" class="zone-user-table-no-login">
          <span v-t="'NCZONE_NO_LOGIN'"></span>
        </div>
        <div v-else class="zone-user-table">
          <nczone-player-login-row v-for="(player, idx) in players" :key="player.id" :idx="idx" :player="player" />
        </div>
      </div>
      <nczone-login-draw-area />
    </div>
  </div>
</template>
<script>
import { mapGetters } from 'vuex'

export default {
  name: 'nczone-player-area',
  computed: {
    ...mapGetters([
      'canViewLogin'
    ]),
    ...mapGetters({
      players: 'loggedInPlayers'
    })
  }
}
</script>
