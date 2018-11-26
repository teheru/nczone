<template>
  <div id="zone-player-area">
    <div class="zone-block">
      <div class="zone-title" v-t="'NCZONE_LOGGEDIN'"></div>
      <div class="zone-content">
        <div v-if="players.length === 0" class="zone-user-table-no-login">
          <span v-t="'NCZONE_NO_LOGIN'"></span>
        </div>
        <div v-else="" class="zone-user-table">
          <nczone-player-login-row v-for="(player, idx) in players" :key="player.id" :idx="idx" :player="player" />
        </div>
      </div>
      <div class="zone-actions" v-if="havePossibleActions">
        <button class="zone-button" v-if="canLogin" @click="doLogin" v-t="'NCZONE_LOGIN'"></button>
        <button class="zone-button" v-if="isLoggedIn" @click="doLogout" v-t="'NCZONE_LOGOUT'"></button>

        <button class="zone-button" v-if="canDraw" @click="doDraw" v-t="'NCZONE_DRAW'"></button>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-player-area',
  computed: {
    ...mapGetters([
      'canDraw',
      'canLogin',
      'isLoggedIn'
    ]),
    ...mapGetters({
      players: 'loggedInPlayers'
    }),
    havePossibleActions () {
      return this.canDraw || this.canLogin || this.isLoggedIn
    }
  },
  methods: {
    doLogin () {
      this.login()
    },
    doLogout () {
      this.logout()
    },
    doDraw () {
      this.drawPreview()
    },
    ...mapActions([
      'login',
      'logout',
      'drawPreview'
    ])
  }
}
</script>
