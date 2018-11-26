<template>
  <div class="zone-bets-table">
    <div class="zone-title" v-t="'NCZONE_BETS_TABLE'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <template v-else="">
        <div v-if="bets.length === 0"><span v-t="'NCZONE_NO_BETTING_PLAYERS'"></span></div>
        <div v-else="" class="zone-bets">
          <div class="zone-bets-table-idx">#</div>
          <div class="zone-bets-table-name" v-t="'NCZONE_TABLE_HEADER_NAME'"></div>
          <div class="zone-bets-table-bets-total" v-t="'NCZONE_TABLE_HEADER_BETS_TOTAL'"></div>
          <div class="zone-bets-table-bets-won" v-t="'NCZONE_TABLE_HEADER_BETS_WON'"></div>
          <div class="zone-bets-table-bets-loss" v-t="'NCZONE_TABLE_HEADER_BETS_LOSS'"></div>
          <div class="zone-bets-table-bets-quota" v-t="'NCZONE_TABLE_HEADER_BETS_QUOTA'"></div>
          <template v-for="(player, idx) in bets">
            <div class="zone-bets-table-idx" :key="`idx-${idx}`">{{ idx+1 }}</div>
            <div class="zone-bets-table-name" :key="`name-${idx}`" v-html="player.username"></div>
            <div class="zone-bets-table-bets-total" :key="`total-${idx}`">{{ player.bets_total || 0 }}</div>
            <div class="zone-bets-table-bets-won" :key="`won-${idx}`">{{ player.bets_won || 0 }}</div>
            <div class="zone-bets-table-bets-loss" :key="`loss-${idx}`">{{ player.bets_loss || 0 }}</div>
            <div class="zone-bets-table-bets-quota" :key="`quota-${idx}`">{{ Math.round(player.bet_quota) || 0 }}%</div>
          </template>
          <div class="zone-bets-table-idx">Ø</div>
          <div class="zone-bets-table-name" v-t="'NCZONE_TABLE_FOOTER_AVERAGE'"></div>
          <div class="zone-bets-table-bets-total">{{ avgBetsTotal }}</div>
          <div class="zone-bets-table-bets-won">{{ avgBetsWon }}</div>
          <div class="zone-bets-table-bets-loss">{{ avgBetsLoss }}</div>
          <div class="zone-bets-table-bets-quota">{{ avgBetsQuota }}%</div>
        </div>
      </template>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-bets',
  computed: {
    avgBetsTotal () {
      return this.avg(this.bets, 'bets_total')
    },
    avgBetsWon () {
      return this.avg(this.bets, 'bets_won')
    },
    avgBetsLoss () {
      return this.avg(this.bets, 'bets_loss')
    },
    avgBetsQuota () {
      return this.avg(this.bets, 'bet_quota')
    },
    ...mapGetters([
      'bets'
    ])
  },
  created () {
    this.fetchData()
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    avg (arr, field) {
      const avg = arr.reduce((acc, cur) => acc + cur[field], 0) / arr.length
      return isNaN(avg) ? 0 : Math.round(avg)
    },
    async fetchData () {
      this.loading = true
      try {
        await this.getBets()
        this.loading = false
      } catch (error) {
        this.error = true
        this.loading = false
      }
    },
    ...mapActions([
      'getBets'
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
