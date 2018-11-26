<template>
  <div class="zone-past-matches">
    <div class="zone-title" v-t="'NCZONE_PMATCHES'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading" v-t="'NCZONE_LOADING'"></div>
      <div class="error" v-else-if="error" v-t="'NCZONE_ERROR_LOADING'"></div>
      <template v-else="">
        <div v-if="matches.length === 0" v-t="'NCZONE_NO_PMATCHES'"></div>
        <nczone-match v-else="" v-for="match in matches" :key="match.id" :match="match" />
      </template>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-past-matches',
  computed: {
    ...mapGetters({
      matches: 'pastMatches'
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
        await this.getPastMatches({ passive: false })
        this.loading = false
      } catch (error) {
        this.error = true
        this.loading = false
      }
    },
    ...mapActions([
      'getPastMatches'
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
