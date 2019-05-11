<template>
  <div class="zone-bets-table">
    <div class="zone-title" v-t="'NCZONE_BETS_TABLE'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <template v-else="">
        <div v-if="players.length === 0"><span v-t="'NCZONE_NO_BETTING_PLAYERS'"></span></div>
        <div v-else="" class="zone-bets">
          <div class="zone-bets-table-idx">#</div>
          <div class="zone-bets-table-name zone-sortable" @click="setSort('username')">
            <span v-t="'NCZONE_TABLE_HEADER_NAME'"></span>
            <nczone-sort-indicator v-if="sort.field === 'username'" :order="sort.order" />
          </div>
          <div class="zone-bets-table-bets-total zone-sortable" @click="setSort('bets_total')">
            <span v-t="'NCZONE_TABLE_HEADER_BETS_TOTAL'"></span>
            <nczone-sort-indicator v-if="sort.field === 'bets_total'" :order="sort.order" />
          </div>
          <div class="zone-bets-table-bets-won zone-sortable" @click="setSort('bets_won')">
            <span v-t="'NCZONE_TABLE_HEADER_BETS_WON'"></span>
            <nczone-sort-indicator v-if="sort.field === 'bets_won'" :order="sort.order" />
          </div>
          <div class="zone-bets-table-bets-loss zone-sortable" @click="setSort('bets_loss')">
            <span v-t="'NCZONE_TABLE_HEADER_BETS_LOSS'"></span>
            <nczone-sort-indicator v-if="sort.field === 'bets_loss'" :order="sort.order" />
          </div>
          <div class="zone-bets-table-bets-quota zone-sortable" @click="setSort('bet_quota')">
            <span v-t="'NCZONE_TABLE_HEADER_BETS_QUOTA'"></span>
            <nczone-sort-indicator v-if="sort.field === 'bet_quota'" :order="sort.order" />
          </div>
          <template v-for="(player, idx) in players">
            <div class="zone-bets-table-idx" :key="`idx-${idx}`">{{ idx+1 }}</div>
            <div class="zone-bets-table-name" :key="`name-${idx}`" v-html="player.username"></div>
            <div class="zone-bets-table-bets-total" :key="`total-${idx}`">{{ player.bets_total || 0 }}</div>
            <div class="zone-bets-table-bets-won" :key="`won-${idx}`">{{ player.bets_won || 0 }}</div>
            <div class="zone-bets-table-bets-loss" :key="`loss-${idx}`">{{ player.bets_loss || 0 }}</div>
            <div class="zone-bets-table-bets-quota" :key="`quota-${idx}`" :class="{'color-positive': (Math.round(player.bet_quota) || 0) > 50, 'color-negative': (Math.round(player.bet_quota) || 0) < 50}">{{ Math.round(player.bet_quota) || 0 }}%</div>
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
import { avg, sort } from '@/functions'
import NczoneSortIndicator from '../components/SortIndicator'

export default {
  name: 'nczone-bets',
  components: {
    NczoneSortIndicator
  },
  computed: {
    players () {
      return sort(this.bets, this.sort)
    },
    avgBetsTotal () {
      return avg(this.bets, 'bets_total')
    },
    avgBetsWon () {
      return avg(this.bets, 'bets_won')
    },
    avgBetsLoss () {
      return avg(this.bets, 'bets_loss')
    },
    avgBetsQuota () {
      return avg(this.bets, 'bet_quota')
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
    setSort (field) {
      if (this.sort.field !== field) {
        this.sort.field = field
      } else {
        this.sort.order *= -1
      }
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
      'getBets'
    ])
  },
  data () {
    return {
      loading: false,
      error: false,
      sort: {
        field: 'bet_quota',
        order: -1
      }
    }
  }
}
</script>
