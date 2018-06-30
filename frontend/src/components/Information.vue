<template>
  <div id="zone_infos" v-if="info" @mouseover="stop" @mouseout="start">
    <div class="zone-title" v-t="'NCZONE_INFORMATION'"></div>
    <div class="zone-content" v-html="info"></div>
  </div>
</template>
<script>
import {mapGetters} from 'vuex'

export default {
  name: 'nczone-information',
  computed: {
    ...mapGetters([
      'info',
      'timer'
    ])
  },
  methods: {
    cb () {
      this.$store.dispatch('nextInformation')
    },
    start () {
      this.timer.every(15, this.cb)
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
