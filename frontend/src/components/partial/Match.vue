<template>
<div class="zone-match">
  <div class="zone-match-title">{{ $t('NCZONE_MATCH_MATCH') }} #{{ match.id }}</div>
  <div class="zone-match-data">
    <template v-if="match.winner">
      <div>{{ $t('NCZONE_MATCH_WINNER') }}</div>
      <div>{{ match.winner }}</div>
    </template>

    <div>{{ $t('NCZONE_MATCH_DRAWER') }}</div>
    <div>{{ match.drawer.name }}</div>

    <template v-if="match.winner">
      <div>{{ $t('NCZONE_MATCH_RESULT_POSTER') }}</div>
      <div>{{ match.result_poster.name }}</div>
    </template>

    <div>{{ $t('NCZONE_MATCH_MAP') }}</div>
    <div>{{ match.map.title }}</div>

    <div>{{ $t('NCZONE_MATCH_CIVS') }}</div>
    <div>
      <div v-if="match.civs.both">
        {{ $t('NCZONE_MATCH_CIVS_BOTH') }}: <nczone-csv :list="match.civs.both.map(c => c.title)"></nczone-csv>
      </div>
      <div v-if="match.civs.team1">
        {{ $t('NCZONE_MATCH_CIVS_TEAM1') }}: <nczone-csv :list="match.civs.team1.map(c => c.title)"></nczone-csv>
      </div>
      <div v-if="match.civs.team2">
        {{ $t('NCZONE_MATCH_CIVS_TEAM2') }}:  <nczone-csv :list="match.civs.team2.map(c => c.title)"></nczone-csv>
      </div>
    </div>

    <template v-if="match.winner">
      <div>{{ $t('NCZONE_MATCH_LENGTH') }}</div>
      <div>{{ matchLength }}</div>
    </template>
    <template v-else="">
      <div>{{ $t('NCZONE_MATCH_TIME_SINCE_DRAW') }}</div>
      <div>{{ matchLength }}</div>
    </template>
  </div>

  <div class="zone-match-title">{{ $t('NCZONE_MATCH_TEAMS') }}:</div>
  <div class="zone-match-team-table">

    <nczone-team :matchId="matchId" :team="1"></nczone-team>

    <div class="zone-match-vs">
      {{ $t('NCZONE_MATCH_VS') }}
    </div>

    <nczone-team :matchId="matchId" :team="2"></nczone-team>

  </div>

  <div v-if="canManage" class="zone-match-post-result-form">
    {{ $t('NCZONE_MATCH_RESULT') }}
    <select>
      <option value="">{{ lang('NCZONE_MATCH_POST_RESULT') }}</option>
      <option value="">{{ lang('NCZONE_MATCH_POST_WIN_TEAM1') }}</option>
      <option value="">{{ lang('NCZONE_MATCH_POST_WIN_TEAM2') }}</option>
      <option value="">{{ lang('NCZONE_MATCH_POST_DRAW') }}</option>
      <option value="">{{ lang('NCZONE_MATCH_POST_OW') }}</option>
    </select>
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
    matchId: {
      type: Number,
      required: true,
    },
  },
  computed: {
    match() {
      return this.matchById(this.matchId)
    },
    matchLength() {
      const hours = parseInt(this.gameSeconds / 3600, 10)
      const minutes = parseInt(this.gameSeconds % 3600 / 60, 10)
      const seconds = parseInt(this.gameSeconds % 3600 % 60, 10)
      return [
        (hours > 9 ? hours : '0' + hours),
        (minutes > 9 ? minutes : '0' + minutes),
        (seconds > 9 ? seconds : '0' + seconds),
      ].join(':')
    },
    canManage() {
      return this.match.players.team1.map(p => p.id).includes(this.me.id) ||
        this.match.players.team2.map(p => p.id).includes(this.me.id)
    },
    ...mapGetters([
      'matchById',
      'me'
    ])
  },
  data() {
    return {
      gameSeconds: 0,
    }
  },
  mounted () {
    if (!this.match.winner) {
      this.$options.interval = setInterval(() => {
        this.gameSeconds = new Date().getTime() / 1000 - this.match.timestampStart
      }, 1000);
    } else {
      this.gameSeconds = this.match.timestampEnd - this.match.timestampStart
    }
  },
  beforeDestroy () {
    clearInterval(this.$options.interval);
  },
}
</script>
