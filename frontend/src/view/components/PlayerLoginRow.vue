<template>
  <div class="zone-player-login-row" :class="activityClass" :title="idleSince">
    <div class="zone-player-table-idx">{{ idx + 1 }}.</div>
    <div class="zone-player-table-kick" v-if="canModLogin">
      <button class="zone-mini-button fa fa-times" @click="modLogout(player.id)"></button>
    </div>
    <div class="zone-player-table-username" v-html="player.username"></div>
    <div class="zone-player-table-rating">{{ player.rating }}</div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'
import { pad } from '@/functions'

export default {
  name: 'nczone-player-login-row',
  props: {
    player: {
      type: Object,
      required: true
    },
    idx: {
      type: Number,
      required: true
    }
  },
  mounted () {
    this.start()
  },
  beforeDestroy () {
    this.stop()
  },
  methods: {
    cb (now) {
      this.t = now / 1000
    },
    start () {
      this.timer.every(5, this.cb)
    },
    stop () {
      this.timer.off(this.cb)
    },
    modLogout (userId) {
      this.logoutPlayer({ userId: userId })
    },
    ...mapActions([
      'logoutPlayer'
    ])
  },
  data () {
    return {
      t: new Date().getTime() / 1000
    }
  },
  computed: {
    ...mapGetters([
      'timer',
      'canModLogin'
    ]),
    activityClass () {
      const diff = this.t - this.player.last_activity
      const breakpoints = [450, 525, 600, 675, 750, 825, 900, 975, 1050, 1125, 1200]
      for (let i = 0; i < breakpoints.length; i++) {
        if (diff < breakpoints[i]) {
          return `zone-player-activity-${i + 1}`
        }
      }
      return `zone-player-activity-${breakpoints.length}`
    },
    idleSince () {
      const diff = this.t - this.player.last_activity

      const hours = parseInt(diff / 3600, 10)
      const min = parseInt(diff % 3600 / 60, 10)
      const sec = parseInt(diff % 3600 % 60, 10)
      return pad(hours) + ':' + pad(min) + ':' + pad(sec)
    }
  }
}
</script>
