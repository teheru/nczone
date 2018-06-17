<template>
  <div>
    <div class="zone-title" v-t="'NCZONE_RMATCHES'"></div>
    <div class="zone-content">
      <nczone-match v-for="(match, idx) in matches" :key="idx" :matchId="match.id"></nczone-match>
    </div>
  </div>
</template>
<script>
import {mapGetters} from 'vuex'
import NczoneMatch from "./partial/Match";

export default {
  name: 'nczone-running-matches',
  components: {NczoneMatch},
  computed: {
    ...mapGetters([
      'matches'
    ])
  },
  created() {
    this.$store.dispatch('getRunningMatches')
  }
}
</script>
