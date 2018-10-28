<template>
  <div class="zone-player-details zone-overlay" v-if="visible">
    <div class="zone-overlay-panel">
      <div class="zone-block">
        <div class="zone-title-bar">
          <div class="zone-title" v-t="'NCZONE_PLAYER_DETAILS_TITLE'"></div>
          <div class="zone-close-button" @click="close">x</div>
        </div>
        <div class="zone-content">
          <div class="zone-player-details-table">
            <div class="zone-player-details-label zone-player-details-name-label" v-t="'NCZONE_PLAYER_DETAILS_NAME'"></div>
            <div class="zone-player-details-name">{{player.username}}</div>
            <div class="zone-player-details-label zone-player-details-games-label" v-t="'NCZONE_PLAYER_DETAILS_GAMES'"></div>
            <div class="zone-player-details-games">
              <span>{{player.wins || 0}}</span> +
              <span>{{player.losses || 0}}</span> =
              <span>{{player.games || 0}}</span>
            </div>
            <div class="zone-player-details-label zone-player-details-winrate-label" v-t="'NCZONE_PLAYER_DETAILS_WINRATE'"></div>
            <div class="zone-player-details-winrate">{{Math.round(player.winrate, 2) || 0}}%</div>
            <div class="zone-player-details-label zone-player-details-rating-label" v-t="'NCZONE_PLAYER_DETAILS_RATING'"></div>
            <div class="zone-player-details-rating">{{player.rating}}</div>
            <div class="zone-player-details-label zone-player-details-streak-label" v-t="'NCZONE_PLAYER_DETAILS_STREAK'"></div>
            <div class="zone-player-details-streak">{{player.streak}}</div>
            <div class="zone-player-details-label zone-player-details-bets-label" v-t="'NCZONE_PLAYER_DETAILS_BETS'"></div>
            <div class="zone-player-details-bets">
              <span>{{player.bets_won || 0}}</span> +
              <span>{{player.bets_loss || 0}}</span> =
              <span>{{(player.bets_won + player.bets_loss) || 0}}</span>
            </div>
            <div class="zone-player-details-label zone-player-details-activity-label" v-t="'NCZONE_PLAYER_DETAILS_ACTIVITY'"></div>
            <div class="zone-player-details-activity">{{player.activity}}</div>
          </div>
          <nczone-player-graph class="zone-player-details-graph" :match-numbers="ratingData.numbers" :ratings="ratingData.ratings"/>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import {mapGetters} from 'vuex'
import NczonePlayerGraph from './PlayerGraph'

export default {
  name: 'nczone-player-details',
  components: {NczonePlayerGraph},
  methods: {
    close () {
      this.$store.dispatch('playerDetailsClose')
    }
  },
  computed: {
    ...mapGetters({
      visible: 'playerDetailsVisible',
      player: 'playerDetailsPlayer',
      ratingData: 'playerDetailsRatingData'
    })
  }
}
</script>
