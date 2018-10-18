<template>
  <div :class="classes">
    <div class="zone-match-bets">
      <div class="zone-match-bets-percentage">
        <a v-if="canBet" @click="bet" class="zone-match-bets-bet-button"></a>
        <div class="zone-match-bets-percentage-number">{{ perc }}%</div>
        <div class="zone-match-bets-percentage-bar" :style="`height: ${perc}%`"></div>
      </div>
      <div class="zone-match-bets-overlay">
        <div class="zone-match-bets-title" v-t="bets.length > 0 ? 'NCZONE_MATCH_HAVE_BET' : 'NCZONE_MATCH_NO_BETS'"></div>
        <ul class="zone-match-betters">
          <li v-for="(bet, idx) in bets" :key="idx"><span v-html="bet.user.username"></span> ({{ renderBetTime(bet.timestamp) }})</li>
        </ul>
      </div>
    </div>

    <div class="zone-match-team">
      <div v-if="canReplace" class="zone-match-team-header zone-match-player-replace"></div>
      <div class="zone-match-team-header zone-match-player-name zone-highlight-color" v-t="title"></div>
      <div class="zone-match-team-header zone-match-player-rating">({{ totalRating }})</div>
      <div v-if="havePlayerCivs" class="zone-match-team-header zone-match-player-civ" v-t="'NCZONE_MATCH_CIVS'"></div>

      <template v-for="(player, idx) in players">
        <div v-if="canReplace" class="zone-match-player-replace" :key="`replace-${idx}`" @click="playerReplace(player.id)">[K]</div>
        <div class="zone-match-player-name zone-highlight-color" :key="`name-${idx}`"><span v-html="player.username"></span><span v-if="match.winner">({{ player.rating_change }})</span></div>
        <div class="zone-match-player-rating" :key="`rating-${idx}`">({{ player.rating }})</div>
        <div v-if="havePlayerCivs" class="zone-match-player-civ" :key="`civ${idx}`"><span v-if="player.civ">{{ $t(player.civ.title) }}</span></div>
      </template>
    </div>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'

const pad = (n) => n > 9 ? n : `0${n}`

export default {
  name: 'nczone-team',
  props: {
    match: {
      type: Object,
      required: true
    },
    team: {
      type: Number,
      required: true
    }
  },
  methods: {
    bet () {
      this.$store.dispatch('bet', {matchId: this.match.id, team: this.team})
    },
    renderBetTime (timestamp) {
      const secondsSinceDraw = timestamp - this.match.timestampStart
      const seconds = secondsSinceDraw % 60
      const minutes = Math.floor(secondsSinceDraw / 60) % 60
      const hours = Math.floor(secondsSinceDraw / 3600)
      return (hours > 0 ? (hours + ':') : '') + pad(minutes) + ':' + pad(seconds)
    },
    playerReplace (userId) {
      this.$store.dispatch('replacePreview', {userId: userId})
    }
  },
  computed: {
    classes () {
      if (!this.isFinished) {
        return [`zone-match-team-${this.team}`]
      }

      if (this.isWinnerTeam) {
        return [`zone-match-team-${this.team}`, 'zone-match-team-winner']
      }

      if (this.isLoserTeam) {
        return [`zone-match-team-${this.team}`, 'zone-match-team-loser']
      }

      return [`zone-match-team-${this.team}`, 'zone-match-team-no-result']
    },
    isFinished () {
      return this.match.timestampEnd > 0
    },
    isWinnerTeam () {
      return this.match.winner && this.team === this.match.winner
    },
    isLoserTeam () {
      return this.match.winner && this.team !== this.match.winner
    },
    canBet () {
      return !this.isFinished &&
        !this.match.bets.team1.map(p => p.user.id).includes(this.me.id) &&
        !this.match.bets.team2.map(p => p.user.id).includes(this.me.id)
    },
    perc () {
      const betCount = this.match.bets.team1.length + this.match.bets.team2.length
      const perc1 = betCount === 0 ? 50 : Math.round(this.match.bets.team1.length * 100 / betCount)
      const perc2 = 100 - perc1
      return this.team === 1 ? perc1 : perc2
    },
    players () {
      return this.team === 1 ? this.match.players.team1 : this.match.players.team2
    },
    bets () {
      return this.team === 1 ? this.match.bets.team1 : this.match.bets.team2
    },
    title () {
      return this.team === 1 ? 'NCZONE_MATCH_TEAM1' : 'NCZONE_MATCH_TEAM2'
    },
    havePlayerCivs () {
      return !!this.players.find(p => !!p.civ)
    },
    totalRating () {
      return this.players.reduce((total, player) => total + player.rating, 0)
    },
    ...mapGetters([
      'me',
      'canReplace'
    ])
  }
}
</script>
