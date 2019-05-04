<template>
  <div class="zone-players-table">
    <div class="zone-title" v-t="'NCZONE_PLAYERS_TABLE'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <template v-else="">
        <div v-if="players.length === 0"><span v-t="'NCZONE_NO_ACTIVE_PLAYERS'"></span></div>
        <div v-else="" class="zone-players">

          <div class="zone-players-table-idx">#</div>
          <div class="zone-players-table-login" v-if="canModLogin"></div>
          <div class="zone-players-table-name zone-players-sortable" @click="setSort('username')">
            <span v-t="'NCZONE_TABLE_HEADER_NAME'"></span>
            <nczone-sort-indicator v-if="sort.field === 'username'" :order="sort.order" />
          </div>
          <div class="zone-players-table-games zone-players-sortable" @click="setSort('games')">
            <span v-t="'NCZONE_TABLE_HEADER_GAMES'"></span>
            <nczone-sort-indicator v-if="sort.field === 'games'" :order="sort.order" />
          </div>
          <div class="zone-players-table-wins zone-players-sortable" @click="setSort('wins')">
            <span v-t="'NCZONE_TABLE_HEADER_WINS'"></span>
            <nczone-sort-indicator v-if="sort.field === 'wins'" :order="sort.order" />
          </div>
          <div class="zone-players-table-losses zone-players-sortable" @click="setSort('losses')">
            <span v-t="'NCZONE_TABLE_HEADER_LOSSES'"></span>
            <nczone-sort-indicator v-if="sort.field === 'losses'" :order="sort.order" />
          </div>
          <div class="zone-players-table-winrate zone-players-sortable" @click="setSort('winrate')">
            <span v-t="'NCZONE_TABLE_HEADER_WINRATE'"></span>
            <nczone-sort-indicator v-if="sort.field === 'winrate'" :order="sort.order" />
          </div>
          <div class="zone-players-table-streak zone-players-sortable" @click="setSort('streak')">
            <span v-t="'NCZONE_TABLE_HEADER_STREAK'"></span>
            <nczone-sort-indicator v-if="sort.field === 'streak'" :order="sort.order" />
          </div>
          <div class="zone-players-table-rating-change zone-players-sortable" @click="setSort('ratingchange')">
            <span v-t="'NCZONE_TABLE_HEADER_RATING_CHANGE'"></span>
            <nczone-sort-indicator v-if="sort.field === 'ratingchange'" :order="sort.order" />
          </div>
          <div class="zone-players-table-rating zone-players-sortable" @click="setSort('rating')">
            <span v-t="'NCZONE_TABLE_HEADER_RATING'"></span>
            <nczone-sort-indicator v-if="sort.field === 'rating'" :order="sort.order" />
          </div>
          <div class="zone-players-table-activity zone-players-sortable" @click="setSort('activity_matches')">
            <span v-t="'NCZONE_TABLE_HEADER_ACTIVITY'"></span>
            <nczone-sort-indicator v-if="sort.field === 'activity_matches'" :order="sort.order" />
          </div>

          <template v-for="(player, idx) in players">
            <div class="zone-players-table-idx" :key="`idx-${idx}`">{{ idx+1 }}</div>
            <div class="zone-players-table-kick" :key="`kick-${idx}`" v-if="canModLogin">
              <button class="zone-mini-button" v-if="player.logged_in === 0" @click="modLogin(player.id)">L</button>
            </div>
            <div class="zone-players-table-name" :key="`name-${idx}`" v-html="player.username" @click="openPlayerDetailsOverlay(player.id)"></div>
            <div class="zone-players-table-games" :key="`games-${idx}`">{{ player.games || 0 }}</div>
            <div class="zone-players-table-wins" :key="`wins-${idx}`">{{ player.wins || 0 }}</div>
            <div class="zone-players-table-losses" :key="`losses-${idx}`">{{ player.losses || 0 }}</div>
            <div class="zone-players-table-winrate" :key="`winrate-${idx}`" :class="{'zone-players-table-winrate-positive': (Math.round(player.winrate) || 0) > 50, 'zone-players-table-winrate-negative': (Math.round(player.winrate) || 0) < 50}">{{ Math.round(player.winrate) || 0 }}%</div>
            <div class="zone-players-table-streak" :key="`streak-${idx}`" :class="{'zone-players-table-streak-positive': (player.streak || 0) > 0, 'zone-players-table-streak-negative': (player.streak || 0) < 0}">{{ player.streak || 0 }}</div>
            <div class="zone-players-table-rating-change" :key="`rating-change-${idx}`" :class="{'zone-players-table-rating-change-positive': (player.ratingchange || 0) > 0, 'zone-players-table-rating-change-negative': (player.ratingchange || 0) < 0}">{{ player.ratingchange || 0 }}</div>
            <div class="zone-players-table-rating" :key="`rating-${idx}`">{{ player.rating || 0 }}</div>
            <div class="zone-players-table-activity" :key="`activity-${idx}`">
              <nczone-activiy :activity="player.activity || 0" />
            </div>
          </template>

          <div class="zone-players-table-idx">Ø</div>
          <div class="zone-players-table-login" v-if="canModLogin"></div>
          <div class="zone-players-table-name" v-t="'NCZONE_TABLE_FOOTER_AVERAGE'"></div>
          <div class="zone-players-table-games">{{ avgGames }}</div>
          <div class="zone-players-table-wins">{{ avgWins }}</div>
          <div class="zone-players-table-losses">{{ avgLosses }}</div>
          <div class="zone-players-table-winrate">{{ avgWinrate }}%</div>
          <div class="zone-players-table-streak">{{ avgStreak }}</div>
          <div class="zone-players-table-rating-change">{{ avgRatingChange }}</div>
          <div class="zone-players-table-rating">{{ avgRating }}</div>
          <div class="zone-players-table-activity">
            <nczone-activiy :activity="avgActivity" />
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'
import { avg } from '@/functions'
import NczoneActiviy from "../components/Activity";
import NczoneSortIndicator from "../components/SortIndicator";

export default {
  name: 'nczone-players-table',
  components: {NczoneSortIndicator, NczoneActiviy},
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
      return avg(this.players, 'games')
    },
    avgWins () {
      return avg(this.players, 'wins')
    },
    avgLosses () {
      return avg(this.players, 'losses')
    },
    avgWinrate () {
      return avg(this.players, 'winrate')
    },
    avgStreak () {
      return avg(this.players, 'streak')
    },
    avgRating () {
      return avg(this.players, 'rating')
    },
    avgRatingChange () {
      return avg(this.players, 'ratingchange')
    },
    avgActivity () {
      return avg(this.players, 'activity')
    },
    ...mapGetters({
      allPlayers: 'players',
      canModLogin: 'canModLogin'
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
    async fetchData () {
      this.loading = true
      try {
        await this.getAllPlayers({ passive: false })
      } catch (error) {
        this.error = true
      } finally {
        this.loading = false
      }
    },
    modLogin (userId) {
      this.loginPlayer({ userId: userId })
    },
    ...mapActions([
      'getAllPlayers',
      'loginPlayer',
      'openPlayerDetailsOverlay'
    ])
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
