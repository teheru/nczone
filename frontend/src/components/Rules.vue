<template>
  <div class="zone-rules">
    <div class="zone-title" v-t="'NCZONE_RULES'"></div>
    <div class="zone-content" v-html="rules"></div>
  </div>
</template>
<script>
import {mapGetters} from 'vuex'

export default {
  name: 'nczone-rules',
  computed: {
    ...mapGetters([
      'rules'
    ])
  },
  created () {
    this.$store.dispatch('getRules')
  }
}
</script>
