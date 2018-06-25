<template>
  <div id="zone-player-area">
    <div class="zone-block">
      <div class="zone-title" v-t="'NCZONE_LOGGEDIN'"></div>
      <div class="zone-content">
        <div v-if="players.length === 0" class="zone-user-table-no-login">
          <span v-if="'NCZONE_NO_LOGIN'"></span>
        </div>
        <div v-else="" class="zone-user-table">
          <nczone-player-login-row v-for="(player, idx) in players" :key="idx" :idx="idx" :player="player"></nczone-player-login-row>
        </div>
      </div>
      <div class="zone-actions">
        <button class="zone-button" v-if="canLogin" @click="login" v-t="'NCZONE_LOGIN'"></button>
        <button class="zone-button" v-if="isLoggedIn" @click="logout" v-t="'NCZONE_LOGOUT'"></button>

        <button class="zone-button" v-if="canDraw" @click="draw" v-t="'NCZONE_DRAW'"></button>
      </div>
    </div>
  </div>
</template>
<script>
import {mapGetters} from 'vuex'
import NczonePlayerLoginRow from './PlayerLoginRow'

export default {
  name: 'nczone-player-area',
  components: {NczonePlayerLoginRow},
  computed: {
    ...mapGetters([
      'canDraw',
      'canLogin',
      'isLoggedIn'
    ]),
    ...mapGetters({
      players: 'loggedInPlayers'
    })
  },
  methods: {
    login () {
      this.$store.dispatch('login')
    },
    logout () {
      this.$store.dispatch('logout')
    },
    draw () {
      this.$store.dispatch('drawPreview')
    }
  }
}
</script>
