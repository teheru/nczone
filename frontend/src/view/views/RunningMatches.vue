<template>
  <div v-if="canViewMatches" class="zone-running-matches">
    <div class="zone-title" v-t="'NCZONE_RMATCHES'"></div>
    <div class="zone-content">
      <nczone-loading v-if="loading"></nczone-loading>
      <div class="error" v-else-if="error" v-t="'NCZONE_ERROR_LOADING'"></div>
      <template v-else>
        <div v-if="matches.length === 0" v-t="'NCZONE_NO_RMATCHES'"></div>
        <nczone-match v-for="match in matches" :key="match.id" :match="match" />
      </template>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-running-matches',
  computed: {
    ...mapGetters({
      canViewMatches: 'canViewMatches',
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
    async fetchData () {
      this.loading = true
      try {
        await this.getRunningMatches({ passive: false })
      } catch (error) {
        this.error = true
      } finally {
        this.loading = false
      }
    },
    ...mapActions([
      'getRunningMatches'
    ])
  },
  data () {
    return {
      loading: false,
      error: false
    }
  }
}
</script>
