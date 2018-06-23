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
      'info'
    ])
  },
  methods: {
    start () {
      this.timer = setTimeout(async () => {
        await this.$store.dispatch('nextInformation')
        this.start()
      }, 15000)
    },
    stop () {
      clearTimeout(this.timer)
      this.timer = null
    }
  },
  data () {
    return {
      timer: null
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
