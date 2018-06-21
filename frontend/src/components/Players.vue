<template>
  <div class="zone-players-table">
    <div class="zone-title" v-t="'NCZONE_TABLE'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading" v-t="'NCZONE_LOADING'"></div>
      <div class="error" v-else-if="error" v-t="'NCZONE_ERROR_LOADING'"></div>
      <template v-else="">
        <div v-if="players.length === 0" v-t="'NCZONE_NO_ACTIVE_PLAYERS'"></div>
        <div v-else="" class="zone-players">
          <div class="zone-players-table-idx">#</div>
          <div class="zone-players-table-name" v-t="'NCZONE_TABLE_HEADER_NAME'"></div>
          <div class="zone-players-table-games" v-t="'NCZONE_TABLE_HEADER_GAMES'"></div>
          <div class="zone-players-table-wins" v-t="'NCZONE_TABLE_HEADER_WINS'"></div>
          <div class="zone-players-table-losses" v-t="'NCZONE_TABLE_HEADER_LOSSES'"></div>
          <div class="zone-players-table-ties" v-t="'NCZONE_TABLE_HEADER_TIES'"></div>
          <div class="zone-players-table-winrate" v-t="'NCZONE_TABLE_HEADER_WINRATE'"></div>
          <div class="zone-players-table-streak" v-t="'NCZONE_TABLE_HEADER_STREAK'"></div>
          <div class="zone-players-table-rating-change" v-t="'NCZONE_TABLE_HEADER_RATING_CHANGE'"></div>
          <div class="zone-players-table-rating">
            <span v-t="'NCZONE_TABLE_HEADER_RATING'"></span>
            (<span v-t="'NCZONE_TABLE_HEADER_STREAK_RATING'"></span>)
          </div>
          <div class="zone-players-table-activity" v-t="'NCZONE_TABLE_HEADER_ACTIVITY'"></div>
          <template v-for="(player, idx) in players">
            <div class="zone-players-table-idx" :key="`idx-${idx}`">{{ idx+1 }}</div>
            <div class="zone-players-table-name" :key="`name-${idx}`">{{ player.username }}</div>
            <div class="zone-players-table-games" :key="`games-${idx}`">{{ player.games }}</div>
            <div class="zone-players-table-wins" :key="`wins-${idx}`">{{ player.wins }}</div>
            <div class="zone-players-table-losses" :key="`losses-${idx}`">{{ player.losses }}</div>
            <div class="zone-players-table-ties" :key="`ties-${idx}`">{{ player.ties }}</div>
            <div class="zone-players-table-winrate" :key="`winrate-${idx}`">{{ player.winrate }}</div>
            <div class="zone-players-table-streak" :key="`winrate-${idx}`">{{ player.streak }}</div>
            <div class="zone-players-table-rating-change" :key="`rating-change-${idx}`">{{ player.ratingchange }}</div>
            <div class="zone-players-table-rating" :key="`rating-${idx}`">{{ player.rating }} ({{ player.streakrating }})</div>
            <div class="zone-players-table-activity" :key="`activity-${idx}`">{{ player.activity }}</div>
          </template>
          <div class="zone-players-table-idx">Ø</div>
          <div class="zone-players-table-name" v-t="'NCZONE_TABLE_FOOTER_AVERAGE'"></div>
          <div class="zone-players-table-games">{{ avgGames }}</div>
          <div class="zone-players-table-wins">{{ avgWins }}</div>
          <div class="zone-players-table-losses">{{ avgLosses }}</div>
          <div class="zone-players-table-ties">{{ avgTies }}</div>
          <div class="zone-players-table-winrate">{{ avgWinrate }}</div>
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
    avgGames () {
      return this.avg(this.players, 'games')
    },
    avgWins () {
      return this.avg(this.players, 'wins')
    },
    avgLosses () {
      return this.avg(this.players, 'losses')
    },
    avgTies () {
      return this.avg(this.players, 'ties')
    },
    avgWinrate () {
      return this.avg(this.players, 'losses')
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
    avgStreakRating () {
      return this.avg(this.players, 'streakrating')
    },
    avgActivity () {
      return this.avg(this.players, 'activity')
    },
    ...mapGetters({
      players: 'allPlayers'
    })
  },
  created () {
    this.fetchData()
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    avg (arr, prop) {
      return arr.reduce((acc, cur) => acc + cur[prop], 0) / arr.length
    },
    fetchData () {
      this.loading = true
      this.$store.dispatch('getAllPlayers')
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
