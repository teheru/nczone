<template>
  <div class="zone-actions" v-if="havePossibleActions">
    <button class="zone-button" v-if="canLogin" @click="doLogin" v-t="'NCZONE_LOGIN'"></button>
    <button class="zone-button" v-if="isLoggedIn" @click="doLogout" v-t="'NCZONE_LOGOUT'"></button>

    <button class="zone-button" v-if="canDraw && !(canBlockDraw && drawBlocked)" @click="doDraw" v-t="'NCZONE_DRAW'" :disabled="drawBlocked > 0">
      <span v-if="drawBlocked">
        &nbsp;<div class="fa fa-lock"></div>
        &nbsp;<nczone-lock-timer v-if="drawBlocked" :lockLengthSeconds="blockSeconds" />
      </span>
    </button>
    <button class="fa fa-unlock zone-button" v-if="canBlockDraw && drawBlocked" @click="doDrawUnblock"></button>
    <button class="fa fa-lock zone-button" v-if="canBlockDraw" @click="doDrawBlock">
      <span v-if="drawBlocked">&nbsp;<nczone-lock-timer :lockLengthSeconds="blockSeconds" /></span>
    </button>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-login-draw-area',
  data () {
    return {
      'blockSeconds': 0
    }
  },
  computed: {
    ...mapGetters([
      'canLogin',
      'drawBlockedTime',
      'canBlockDraw',
      'canDraw',
      'isLoggedIn',
      'timer'
    ]),
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
      this.timer.every(0.25, this.cb)
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
