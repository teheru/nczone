<template>
<div class="zone-match">
  <div class="zone-match-title">{{ $t('NCZONE_MATCH_MATCH') }} #{{ match.id }}</div>
  <div class="zone-match-data">
    <template v-if="match.timestampEnd">
      <div v-t="'NCZONE_MATCH_WINNER'"></div>
      <div v-if="match.winner"><span v-t="'NCZONE_MATCH_TEAM'"></span> {{ match.winner }}</div>
      <div v-else="" v-t="'NCZONE_MATCH_WINNER_NO_RESULT'"></div>
    </template>

    <div v-t="'NCZONE_MATCH_DRAWER'"></div>
    <div>{{ match.drawer.name }}</div>

    <template v-if="match.result_poster">
      <div v-t="'NCZONE_MATCH_RESULT_POSTER'"></div>
      <div>{{ match.result_poster.name }}</div>
    </template>

    <template v-if="match.map">
      <div v-t="'NCZONE_MATCH_MAP'"></div>
      <div>{{ match.map.title }}</div>
    </template>

    <template v-if="haveGlobalCivs">
      <div v-t="'NCZONE_MATCH_CIVS'"></div>
      <div>
        <div v-if="match.civs.both.length > 0">
          <span v-t="'NCZONE_MATCH_CIVS_BOTH'"></span>: <nczone-csv :list="match.civs.both.map(c => $t(c.title))"></nczone-csv>
        </div>
        <div v-if="match.civs.team1.length > 0">
          <span v-t="'NCZONE_MATCH_CIVS_TEAM1'"></span>: <nczone-csv :list="match.civs.team1.map(c => $t(c.title))"></nczone-csv>
        </div>
        <div v-if="match.civs.team2.length > 0">
          <span v-t="'NCZONE_MATCH_CIVS_TEAM2'"></span>:  <nczone-csv :list="match.civs.team2.map(c => $t(c.title))"></nczone-csv>
        </div>
      </div>
    </template>

    <template v-if="match.timestampEnd">
      <div v-t="'NCZONE_MATCH_LENGTH'"></div>
      <div>{{ matchLength }}</div>
    </template>
    <template v-else="">
      <div v-t="'NCZONE_MATCH_TIME_SINCE_DRAW'"></div>
      <div>{{ matchLength }}</div>
    </template>
  </div>

  <div class="zone-match-title" v-t="'NCZONE_MATCH_TEAMS'"></div>
  <div class="zone-match-team-table">

    <nczone-team :match="match" :team="1"></nczone-team>

    <div class="zone-match-vs" v-t="'NCZONE_MATCH_VS'"></div>

    <nczone-team :match="match" :team="2"></nczone-team>

  </div>

  <div v-if="canManage && match.timestampEnd === 0" class="zone-match-post-result-form">
    <span v-t="'NCZONE_MATCH_RESULT'"></span>
    <select v-model="matchResult">
      <option v-for="(opt, idx) in matchResultOptions" :key="idx" :value="opt.value" v-t="opt.title"></option>
    </select>
    <button class="zone-button" v-t="'NCZONE_MATCH_SEND_RESULT'" @click="sendResult" :disabled="matchResult === '0'"></button>
  </div>
</div>
</template>
<script>
import {mapGetters} from 'vuex'
import NczoneCsv from './Csv'
import NczoneTeam from './Team'

export default {
  name: 'nczone-match',
  components: {NczoneTeam, NczoneCsv},
  props: {
    match: {
      type: Object,
      required: true
    }
  },
  methods: {
    sendResult () {
      this.$store.dispatch('postMatchResult', {matchId: this.match.id, winner: this.matchResult})
    },
    cb (now, ticks) {
      this.gameSeconds = new Date().getTime() / 1000 - this.match.timestampStart
    },
    start () {
      this.timer.every(1, this.cb)
    },
    stop () {
      this.timer.off(this.cb)
    }
  },
  computed: {
    matchLength () {
      const hours = parseInt(this.gameSeconds / 3600, 10)
      const minutes = parseInt(this.gameSeconds % 3600 / 60, 10)
      const seconds = parseInt(this.gameSeconds % 3600 % 60, 10)
      return [
        (hours > 9 ? hours : '0' + hours),
        (minutes > 9 ? minutes : '0' + minutes),
        (seconds > 9 ? seconds : '0' + seconds)
      ].join(':')
    },
    haveGlobalCivs () {
      return this.match.civs.both.length > 0 ||
        this.match.civs.team1.length > 0 ||
        this.match.civs.team2.length > 0
    },
    canManage () {
      return this.match.players.team1.map(p => p.id).includes(this.me.id) ||
        this.match.players.team2.map(p => p.id).includes(this.me.id) ||
        this.canModPost
    },
    ...mapGetters([
      'me',
      'timer',
      'canModPost'
    ])
  },
  data () {
    return {
      gameSeconds: 0,
      matchResult: '0',
      matchResultOptions: [
        {
          value: '0',
          title: 'NCZONE_MATCH_POST_RESULT'
        },
        {
          value: '1',
          title: 'NCZONE_MATCH_POST_WIN_TEAM1'
        },
        {
          value: '2',
          title: 'NCZONE_MATCH_POST_WIN_TEAM2'
        },
        {
          value: '3',
          title: 'NCZONE_MATCH_POST_OW'
        }
      ]
    }
  },
  mounted () {
    if (!this.match.timestampEnd) {
      this.start()
    } else {
      this.gameSeconds = this.match.timestampEnd - this.match.timestampStart
    }
  },
  beforeDestroy () {
    this.stop()
  }
}
</script>
