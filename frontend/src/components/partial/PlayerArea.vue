<template>
  <div id="zone-player-area">
    <div class="zone-block">
      <div class="zone-title" v-t="'NCZONE_LOGGEDIN'"></div>
      <div class="zone-content">

        <div v-if="players.length === 0" class="zone-user-table-no-login" v-t="'NCZONE_NO_LOGIN'">
        </div>
        <table v-else="" class="zone-user-table">
          <tr v-for="(player, idx) in players" :key="idx">
            <td class="username">{{ player.username }}</td>
            <td class="rating">{{ player.rating }}</td>
          </tr>
        </table>

        <a v-if="canLogin" @click="login" v-t="'NCZONE_LOGIN'"></a>
        <a v-if="canLogout" @click="logout" v-t="'NCZONE_LOGOUT'"></a>

        <a v-if="canDraw" @click="draw" v-t="'NCZONE_DRAW'"></a>
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
      'canLogout'
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
