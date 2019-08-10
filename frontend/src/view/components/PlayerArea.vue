<template>
  <div v-if="canViewLogin || havePossibleActions" id="zone-player-area">
    <div class="zone-block">
      <div class="zone-title" v-t="'NCZONE_LOGGEDIN'"></div>
      <div v-if="canViewLogin" class="zone-content">
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

        <button class="zone-button" v-if="canDraw && !(canBlockDraw && drawBlocked)" @click="doDraw" v-t="'NCZONE_DRAW'">
          <template v-if="drawBlocked">&nbsp;<div class="fa fa-lock"></div></template>
        </button>
        <button class="zone-button fa fa-unlock" v-if="canBlockDraw && drawBlocked" @click="doDrawUnblock"></button>
        <button class="zone-button fa fa-lock" v-if="canBlockDraw" @click="doDrawBlock"></button>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-player-area',
  data () {
    return {
      'blockSeconds': 0
    }
  },
  computed: {
    ...mapGetters([
      'canViewLogin',
      'canLogin',
      'drawBlockedTime',
      'canBlockDraw',
      'canDraw',
      'isLoggedIn',
      'timer'
    ]),
    ...mapGetters({
      players: 'loggedInPlayers'
    }),
    havePossibleActions () {
      return this.canDraw || this.canLogin || this.isLoggedIn || this.canBlockDraw
    },
    drawBlocked () {
      return this.blockSeconds > 0
    }
  },
  methods: {
    doLogin () {
      this.getDrawBlockedTime()
      this.login()
    },
    doLogout () {
      this.logout()
    },
    doDrawBlock () {
      this.drawBlock()
    },
    doDrawUnblock () {
      this.drawUnblock()
    },
    doDraw () {
      this.getDrawBlockedTime()
      if (!this.drawBlocked) {
        this.openDrawPreviewOverlay()
      }
    },
    ...mapActions([
      'login',
      'logout',
      'getDrawBlockedTime',
      'drawBlock',
      'drawUnblock',
      'openDrawPreviewOverlay'
    ]),
    cb (now) {
      this.blockSeconds = this.drawBlockedTime - now / 1000
    },
    start () {
      this.timer.every(2, this.cb)
    },
    stop () {
      this.timer.off(this.cb)
    }
  },
  mounted () {
    this.start()
  },
  beforeDestroy () {
    this.stop()
  }
}
</script>
