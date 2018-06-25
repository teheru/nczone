<template>
  <div class="zone-running-matches">
    <div class="zone-title" v-t="'NCZONE_RMATCHES'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading" v-t="'NCZONE_LOADING'"></div>
      <div class="error" v-else-if="error" v-t="'NCZONE_ERROR_LOADING'"></div>
      <template v-else="">
        <div v-if="matches.length === 0" v-t="'NCZONE_NO_RMATCHES'"></div>
        <nczone-match v-for="(match, idx) in matches" :key="idx" :match="match"></nczone-match>
      </template>
    </div>
  </div>
</template>
<script>
import {mapGetters} from 'vuex'
import NczoneMatch from './partial/Match'

export default {
  name: 'nczone-running-matches',
  components: {NczoneMatch},
  computed: {
    ...mapGetters({
      matches: 'runningMatches'
    })
  },
  created () {
    this.fetchData()
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    fetchData () {
      this.loading = true
      this.$store.dispatch('getRunningMatches')
        .then(_ => {
          this.loading = false
        })
        .catch(_ => {
          this.error = true
          this.loading = false
        })
    }
  },
  data () {
    return {
      loading: false,
      error: false
    }
  }
}
</script>
