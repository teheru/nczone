<template>
  <div class="zone-player-login-row" :class="activityClass">
    <div class="zone-player-table-idx">{{ idx + 1 }}.</div>
    <div class="zone-player-table-username">{{ player.username }}</div>
    <div class="zone-player-table-rating">{{ player.rating }}</div>
  </div>
</template>
<script>
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
    // todo: should not really start a timer for each row. hmmm
    start () {
      this.timer = setTimeout(async () => {
        this.t = new Date().getTime() / 1000
        this.start()
      }, 10000)
    },
    stop () {
      clearTimeout(this.timer)
      this.timer = null
    }
  },
  data () {
    return {
      t: new Date().getTime() / 1000,
      timer: null
    }
  },
  computed: {
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
