<template>
  <div class="zone-players-table">
    <div class="zone-title" v-t="'NCZONE_TABLE'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <template v-else="">
        <div v-if="players.length === 0"><span v-t="'NCZONE_NO_ACTIVE_PLAYERS'"></span></div>
        <div v-else="" class="zone-players">
          <div class="zone-players-table-idx">#</div>
          <div class="zone-players-table-name" @click="setSort('username')" v-t="'NCZONE_TABLE_HEADER_NAME'"></div>
          <div class="zone-players-table-games" @click="setSort('games')" v-t="'NCZONE_TABLE_HEADER_GAMES'"></div>
          <div class="zone-players-table-wins" @click="setSort('wins')" v-t="'NCZONE_TABLE_HEADER_WINS'"></div>
          <div class="zone-players-table-losses" @click="setSort('losses')" v-t="'NCZONE_TABLE_HEADER_LOSSES'"></div>
          <div class="zone-players-table-winrate" @click="setSort('winrate')" v-t="'NCZONE_TABLE_HEADER_WINRATE'"></div>
          <div class="zone-players-table-streak" @click="setSort('streak')" v-t="'NCZONE_TABLE_HEADER_STREAK'"></div>
          <div class="zone-players-table-rating-change" @click="setSort('ratingchange')" v-t="'NCZONE_TABLE_HEADER_RATING_CHANGE'"></div>
          <div class="zone-players-table-rating" @click="setSort('rating')" v-t="'NCZONE_TABLE_HEADER_RATING'"></div>
          <div class="zone-players-table-activity" @click="setSort('activity')" v-t="'NCZONE_TABLE_HEADER_ACTIVITY'"></div>
          <template v-for="(player, idx) in players">
            <div class="zone-players-table-idx" :key="`idx-${idx}`">{{ idx+1 }}</div>
            <div class="zone-players-table-name" :key="`name-${idx}`" v-html="player.username"></div>
            <div class="zone-players-table-games" :key="`games-${idx}`">{{ player.games || 0 }}</div>
            <div class="zone-players-table-wins" :key="`wins-${idx}`">{{ player.wins || 0 }}</div>
            <div class="zone-players-table-losses" :key="`losses-${idx}`">{{ player.losses || 0 }}</div>
            <div class="zone-players-table-winrate" :key="`winrate-${idx}`">{{ Math.round(player.winrate) || 0 }}%</div>
            <div class="zone-players-table-streak" :key="`streak-${idx}`">{{ player.streak || 0 }}</div>
            <div class="zone-players-table-rating-change" :key="`rating-change-${idx}`">{{ player.ratingchange || 0 }}</div>
            <div class="zone-players-table-rating" :key="`rating-${idx}`">{{ player.rating || 0 }}</div>
            <div class="zone-players-table-activity" :key="`activity-${idx}`">{{ player.activity || 0 }}</div>
          </template>
          <div class="zone-players-table-idx">Ø</div>
          <div class="zone-players-table-name" v-t="'NCZONE_TABLE_FOOTER_AVERAGE'"></div>
          <div class="zone-players-table-games">{{ avgGames }}</div>
          <div class="zone-players-table-wins">{{ avgWins }}</div>
          <div class="zone-players-table-losses">{{ avgLosses }}</div>
          <div class="zone-players-table-winrate">{{ avgWinrate }}%</div>
          <div class="zone-players-table-streak">{{ avgStreak }}</div>
          <div class="zone-players-table-rating-change">{{ avgRatingChange }}</div>
          <div class="zone-players-table-rating">{{ avgRating }}</div>
          <div class="zone-players-table-activity">{{ avgActivity }}</div>
        </div>
      </template>
    </div>
  </div>
</template>
<script>
import {mapGetters} from 'vuex'

export default {
  name: 'nczone-players-table',
  computed: {
    players () {
      const p = this.allPlayers
      return p.sort((a, b) => {
        if (a[this.sort.field] === b[this.sort.field]) {
          return 0
        }
        return (a[this.sort.field] > b[this.sort.field] ? 1 : -1) * this.sort.order
      })
    },
    avgGames () {
      return this.avg(this.players, 'games')
    },
    avgWins () {
      return this.avg(this.players, 'wins')
    },
    avgLosses () {
      return this.avg(this.players, 'losses')
    },
    avgWinrate () {
      return this.avg(this.players, 'winrate')
    },
    avgStreak () {
      return this.avg(this.players, 'streak')
    },
    avgRating () {
      return this.avg(this.players, 'rating')
    },
    avgRatingChange () {
      return this.avg(this.players, 'ratingchange')
    },
    avgActivity () {
      return this.avg(this.players, 'activity')
    },
    ...mapGetters({
      allPlayers: 'players'
    })
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
    avg (arr, field) {
      const avg = arr.reduce((acc, cur) => acc + cur[field], 0) / arr.length
      return isNaN(avg) ? 0 : Math.round(avg)
    },
    fetchData () {
      this.loading = true
      this.$store.dispatch('getAllPlayers', {passive: false})
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
      error: false,
      sort: {
        field: 'rating',
        order: -1
      }
    }
  }
}
</script>
