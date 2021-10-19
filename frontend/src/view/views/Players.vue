<template>
  <div class="zone-players-table">
    <div class="zone-title" v-t="'NCZONE_PLAYERS_TABLE'"></div>
    <div class="zone-content">
      <div v-if="loading" class="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div v-else-if="error" class="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <div v-else-if="players.length === 0"><span v-t="'NCZONE_NO_ACTIVE_PLAYERS'"></span></div>
      <div v-else class="zone-players" :class="{'zone-players-mod': canModLogin}">
        <div class="zone-table-row zone-table-head-row">
          <nczone-table-header-col label="#" />
          <nczone-table-header-col v-if="canModLogin" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_NAME" sort-field="username" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_GAMES" sort-field="games" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_WINS" sort-field="wins" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_LOSSES" sort-field="losses" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_WINRATE" sort-field="winrate" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_STREAK" sort-field="streak" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_RATING_CHANGE" sort-field="ratingchange" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_RATING" sort-field="rating" />
          <nczone-table-header-col label="NCZONE_TABLE_HEADER_ACTIVITY" sort-field="activity_matches" />
        </div>

        <div class="zone-table-row" v-for="(player, idx) in players" :key="`row-${idx}`">
          <div>{{ idx+1 }}</div>
          <div v-if="canModLogin">
            <button class="zone-mini-button fa fa-sign-in" v-show="(player.logged_in === 0) && canModLogin" @click="modLogin(player.id)"></button>
          </div>
          <div class="zone-clickable" v-html="player.username" @click="openPlayerDetailsOverlay(player.id)"></div>
          <div>{{ player.games || 0 }}</div>
          <div>{{ player.wins || 0 }}</div>
          <div>{{ player.losses || 0 }}</div>
          <div :class="{'color-positive': (Math.round(player.winrate) || 0) > 50, 'color-negative': (Math.round(player.winrate) || 0) < 50}">{{ Math.round(player.winrate) || 0 }}%</div>
          <div :class="{'color-positive': (player.streak || 0) > 0, 'color-negative': (player.streak || 0) < 0}">{{ player.streak || 0 }}</div>
          <div :class="{'color-positive': (player.ratingchange || 0) > 0, 'color-negative': (player.ratingchange || 0) < 0}">{{ player.ratingchange || 0 }}</div>
          <div>{{ player.rating || 0 }}</div>
          <div>
            <nczone-activity :activity="player.activity || 0" :activity_matches="player.activity_matches || 0" />
          </div>
        </div>

        <div class="zone-table-row">
          <div>Ø</div>
          <div v-if="canModLogin"></div>
          <div v-t="'NCZONE_TABLE_FOOTER_AVERAGE'"></div>
          <div>{{ avgGames }}</div>
          <div>{{ avgWins }}</div>
          <div>{{ avgLosses }}</div>
          <div>{{ avgWinrate }}%</div>
          <div>{{ avgStreak }}</div>
          <div>{{ avgRatingChange }}</div>
          <div>{{ avgRating }}</div>
          <div>
            <nczone-activity :activity="avgActivity" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'
import { avg } from '@/functions'

export default {
  name: 'nczone-players-table',
  computed: {
    avgGames () {
      return this.avgField('games')
    },
    avgWins () {
      return this.avgField('wins')
    },
    avgLosses () {
      return this.avgField('losses')
    },
    avgWinrate () {
      return this.avgField('winrate')
    },
    avgStreak () {
      return this.avgField('streak')
    },
    avgRating () {
      return this.avgField('rating')
    },
    avgRatingChange () {
      return this.avgField('ratingchange')
    },
    avgActivity () {
      return this.avgField('activity')
    },
    ...mapGetters([
      'players',
      'canModLogin'
    ])
  },
  created () {
    this.fetchData()
    this.setSort({ field: 'rating', order: -1 })
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    avgField (field) {
      return avg(this.players, field)
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
      'setSort',
      'getAllPlayers',
      'loginPlayer',
      'openPlayerDetailsOverlay'
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
