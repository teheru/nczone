<template>
  <div :class="`zone-match-team-${team}`">

    <div class="zone-match-bets">
      <div class="zone-match-bets-percentage">
        <div class="zone-match-bets-percentage-number">{{ perc }}%</div>
        <div class="zone-match-bets-percentage-bar" :style="`height: ${perc}%`"></div>
      </div>
      <div class="zone-match-bets-overlay">
        <div class="zone-match-bets-title" v-t="'NCZONE_MATCH_HAVE_BET'"></div>
        <ul class="zone-match-betters">
          <li v-for="(bet, idx) in bets" :key="idx">{{ bet.user.name }} ({{ bet.timestamp }})</li>
        </ul>
      </div>
    </div>

    <div class="zone-match-team">
      <div class="zone-match-team-header zone-match-player-name" v-t="title"></div>
      <div class="zone-match-team-header zone-match-player-rating">({{ totalRating }})</div>
      <div v-if="havePlayerCivs" class="zone-match-team-header zone-match-player-civ" v-t="'NCZONE_MATCH_CIVS'"></div>

      <template v-for="(player, idx) in players">
        <div class="zone-match-player-name" :key="`name-${idx}`">{{ player.name }}<span v-if="match.winner">({{ player.rating_change }})</span></div>
        <div class="zone-match-player-rating" :key="`rating-${idx}`">({{ player.rating }})</div>
        <div v-if="havePlayerCivs" class="zone-match-player-civ" :key="`civ${idx}`"><span v-if="player.civ">{{ $t(player.civ.title) }}</span></div>
      </template>
    </div>
  </div>
</template>

<script>
import {mapGetters} from 'vuex'
export default {
  name: 'nczone-team',
  props: {
    matchId: {
      type: Number,
      required: true
    },
    team: {
      type: Number,
      required: true
    }
  },
  computed: {
    match () {
      return this.matchById(this.matchId)
    },
    canManage () {
      return this.match.players.team1.map(p => p.id).includes(this.me.id) ||
        this.match.players.team2.map(p => p.id).includes(this.me.id)
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
      let r = 0
      this.players.forEach(p => {
        r += p.rating
      })
      return r
    },
    ...mapGetters([
      'matchById',
      'me'
    ])
  }
}
</script>
