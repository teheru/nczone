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
            <div class="zone-player-details-name" v-html="player.username"></div>
            <div class="zone-player-details-label zone-player-details-games-label" v-t="'NCZONE_PLAYER_DETAILS_GAMES'"></div>
            <div class="zone-player-details-games">
              <span>{{player.wins || 0}}</span> +
              <span>{{player.losses || 0}}</span> =
              <span>{{player.games || 0}}</span>
            </div>
            <div class="zone-player-details-label zone-player-details-winrate-label" v-t="'NCZONE_PLAYER_DETAILS_WINRATE'"></div>
            <div class="zone-player-details-winrate">{{Math.round(player.winrate * 100) / 100 || 0}}%</div>
            <div class="zone-player-details-label zone-player-details-rating-label" v-t="'NCZONE_PLAYER_DETAILS_RATING'"></div>
            <div class="zone-player-details-rating">{{player.rating}} ({{details.rating_max}} / {{details.rating_min}})</div>
            <div class="zone-player-details-label zone-player-details-puntos-label" v-t="'NCZONE_PLAYER_DETAILS_PUNTOS'"></div>
            <div class="zone-player-details-puntos">{{player.ratingchange}} ({{details.rating_change_max}} / {{details.rating_change_min}})</div>
            <div class="zone-player-details-label zone-player-details-streak-label" v-t="'NCZONE_PLAYER_DETAILS_STREAK'"></div>
            <div class="zone-player-details-streak">{{player.streak}} ({{details.streak_max}} / {{details.streak_min}})</div>
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
          <div class="zone-player-details-dreamteams">
            <div class="dreamteams_title" v-t="'NCZONE_DREAMTEAMS_TITLE'"></div>
            <div class="nightmareteams_title" v-t="'NCZONE_NIGHTMARETEAMS_TITLE'"></div>
            <table class="left">
              <tr>
                <th v-t="'NCZONE_DREAMTEAMS_PLAYER1'"></th>
                <th v-t="'NCZONE_DREAMTEAMS_PLAYER2'"></th>
                <th v-t="'NCZONE_DREAMTEAMS_WINSLOSS'"></th>
              </tr>
              <template v-for="(dreamteam, idx) in dreamteams">
                <tr :key="`idx-${idx}`">
                  <td class="username" v-html="dreamteam.user1_name"></td>
                  <td class="username" v-html="dreamteam.user2_name"></td>
                  <td class="matches">{{dreamteam.matches_won}} / {{dreamteam.matches_loss}}</td>
                </tr>
              </template>
            </table>
            <table class="right">
              <tr>
                <th v-t="'NCZONE_DREAMTEAMS_PLAYER1'"></th>
                <th v-t="'NCZONE_DREAMTEAMS_PLAYER2'"></th>
                <th v-t="'NCZONE_DREAMTEAMS_WINSLOSS'"></th>
              </tr>
              <template v-for="(nightmareteam, idx) in nightmareteams">
                <tr :key="`idx-${idx}`">
                  <td class="username" v-html="nightmareteam.user1_name"></td>
                  <td class="username" v-html="nightmareteam.user2_name"></td>
                  <td class="matches">{{nightmareteam.matches_won}} / {{nightmareteam.matches_loss}}</td>
                </tr>
              </template>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-player-details',
  methods: {
    close () {
      this.playerDetailsClose()
    },
    ...mapActions([
      'playerDetailsClose'
    ])
  },
  computed: {
    ...mapGetters({
      visible: 'playerDetailsVisible',
      player: 'playerDetailsPlayer',
      details: 'playerDetails',
      dreamteams: 'playerDetailsDreamteams',
      nightmareteams: 'playerDetailsNightmareteams',
      ratingData: 'playerDetailsRatingData'
    })
  }
}
</script>
