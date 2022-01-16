<template>
  <div v-if="canViewBets" class="zone-bets-table">
    <div class="zone-title" v-t="'NCZONE_BETS_TABLE'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <div v-else-if="bets.length === 0"><span v-t="'NCZONE_NO_BETTING_PLAYERS'"></span></div>
      <div v-else class="zone-bets">
        <div class="zone-table-row zone-table-head-row">
          <nczone-table-header-col label="#" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_NAME" sort-field="username" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_BETS_TOTAL" sort-field="bets_total" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_BETS_WON" sort-field="bets_won" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_BETS_LOSS" sort-field="bets_loss" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_BET_POINTS" sort-field="bet_points" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_BETS_QUOTA" sort-field="bet_quota" />
        </div>

        <div class="zone-table-row" v-for="(player, idx) in bets" :key="`row-${idx}`">
          <div>{{ idx+1 }}</div>
          <div v-html="player.username"></div>
          <div>{{ player.bets_total || 0 }}</div>
          <div>{{ player.bets_won || 0 }}</div>
          <div>{{ player.bets_loss || 0 }}</div>
          <div>{{ player.bet_points || 0.0 }}</div>
          <div :class="{'color-positive': (Math.round(player.bet_quota) || 0) > 50, 'color-negative': (Math.round(player.bet_quota) || 0) < 50}">{{ Math.round(player.bet_quota) || 0 }}%</div>
        </div>

        <div class="zone-table-row">
          <div>Ø</div>
          <div v-t="'NCZONE_TABLE_FOOTER_AVERAGE'"></div>
          <div>{{ avgBetsTotal }}</div>
          <div>{{ avgBetsWon }}</div>
          <div>{{ avgBetsLoss }}</div>
          <div>{{ avgBetPoints }}</div>
          <div>{{ avgBetQuota }}%</div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'
import { avg } from '@/functions'

export default {
  name: 'nczone-bets',
  computed: {
    avgBetsTotal () {
      return this.avgField('bets_total')
    },
    avgBetsWon () {
      return this.avgField('bets_won')
    },
    avgBetsLoss () {
      return this.avgField('bets_loss')
    },
    avgBetPoints () {
      return this.avgField('bet_points')
    },
    avgBetQuota () {
      return this.avgField('bet_quota')
    },
    ...mapGetters([
      'canViewBets',
      'bets'
    ])
  },
  created () {
    this.fetchData()
    this.setSort({ field: 'bet_points', order: -1 })
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    avgField (field) {
      return avg(this.bets, field)
    },
    async fetchData () {
      this.loading = true
      try {
        await this.getBets()
      } catch (error) {
        this.error = true
      } finally {
        this.loading = false
      }
    },
    ...mapActions([
      'setSort',
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
