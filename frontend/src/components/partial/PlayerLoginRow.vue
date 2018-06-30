<template>
  <div class="zone-player-login-row" :class="activityClass">
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
    cb (now, ticks) {
      this.t = now / 1000
    },
    start () {
      this.timer.every(5, this.cb)
    },
    stop () {
      this.timer.off(this.cb)
    }
  },
  data () {
    return {
      t: new Date().getTime() / 1000
    }
  },
  computed: {
    ...mapGetters([
      'timer'
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
    }
  }
}
</script>
