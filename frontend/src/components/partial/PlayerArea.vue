<template>
  <div id="zone-player-area">
    <div class="zone-block">
      <div class="zone-title" v-t="'NCZONE_LOGGEDIN'"></div>
      <div class="zone-content">

        <div v-if="players.length === 0" class="zone-user-table-no-login" v-t="'NCZONE_NO_LOGIN'">
        </div>
        <table v-else="" class="zone-user-table">
          <tr v-for="(player, idx) in players" :key="idx">
            <td>{{ idx + 1 }}.</td>
            <td class="username">{{ player.username }}</td>
            <td class="rating">{{ player.rating }}</td>
          </tr>
        </table>

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
