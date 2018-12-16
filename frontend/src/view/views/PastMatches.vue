<template>
  <div class="zone-past-matches">
    <div class="zone-title" v-t="'NCZONE_PMATCHES'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading" v-t="'NCZONE_LOADING'"></div>
      <div class="error" v-else-if="error" v-t="'NCZONE_ERROR_LOADING'"></div>
      <template v-else="">
        <div v-if="matches.length === 0" v-t="'NCZONE_NO_PMATCHES'"></div>
        <nczone-match v-else="" v-for="match in matches.items" :key="match.id" :match="match" />
        <div class="zone-pagination">
          <span class="zone-button" v-if="matches.page > 0" @click="decrPage">&lt;</span>
          <span class="zone-button" v-if="matches.page > 0" @click="setPage(0)">1</span>
          <span v-if="matches.page > 1">…</span>
          <span class="zone-button">{{ matches.page + 1 }}</span>
          <span v-if="matches.page < matches.total - 1">…</span>
          <span class="zone-button" v-if="matches.page < matches.total" @click="setPage(matches.total)">{{ matches.total + 1 }}</span>
          <span class="zone-button" v-if="matches.page < matches.total" @click="incrPage">&gt;</span>
        </div>
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
    this.matches.page = 0
    this.fetchData()
    window.addEventListener('keyup', this.paginator)
  },
  beforeDestroy () {
    window.removeEventListener('keyup', this.paginator)
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    async fetchData () {
      this.loading = true
      try {
        await this.getPastMatchesPages()
        await this.getPastMatches({ passive: false, page: this.matches.page })
      } catch (error) {
        this.error = true
      } finally {
        this.loading = false
      }
    },
    async setPage (page) {
      this.setPastMatchesPage({ page: page })
    },
    async incrPage () {
      if (this.matches.page < this.matches.total) {
        this.setPage(this.matches.page + 1)
      }
    },
    async decrPage () {
      if (this.matches.page > 0) {
        this.setPage(this.matches.page - 1)
      }
    },
    paginator (e) {
      if (e.key === 'ArrowLeft') {
        this.decrPage()
      } else if (e.key === 'ArrowRight') {
        this.incrPage()
      }
    },
    ...mapActions([
      'getPastMatches',
      'getPastMatchesPages',
      'setPastMatchesPage'
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
