<template>
  <div class="zone-past-matches">
    <div class="zone-title" v-t="'NCZONE_PMATCHES'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading" v-t="'NCZONE_LOADING'"></div>
      <div class="error" v-else-if="error" v-t="'NCZONE_ERROR_LOADING'"></div>
      <template v-else="">
        <div v-if="matches.length === 0" v-t="'NCZONE_NO_PMATCHES'"></div>
        <nczone-match v-else="" v-for="(match, idx) in matches" :key="idx" :matchId="match.id"></nczone-match>
      </template>
    </div>
  </div>
</template>
<script>
import {mapGetters} from 'vuex'
import NczoneMatch from './partial/Match'
export default {
  name: 'nczone-past-matches',
  components: {NczoneMatch},
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
    fetchData () {
      this.loading = true
      this.$store.dispatch('getPastMatches')
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
