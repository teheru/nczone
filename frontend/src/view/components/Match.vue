<template>
<div class="zone-match" :class="{'zone-match-finished': match.post_time > 0}">
  <div class="zone-match-title" v-if="match.post_time">
    {{ $t('NCZONE_MATCH_MATCH') }} #{{ match.id }}<span v-if="!standalone"> &emsp; <a v-bind:href="match.forum_topic_link" class="zone-match-link"> » {{ $t('NCZONE_MATCH_TO_TOPIC') }}</a></span>
  </div>
  <div class="zone-match-title" v-else>{{ $t('NCZONE_MATCH_MATCH') }} #{{ match.id }}</div>
  <div class="zone-match-data">
    <template v-if="match.post_time">
      <div v-t="'NCZONE_MATCH_WINNER'"></div>
      <div v-if="match.winner"><span v-t="'NCZONE_MATCH_TEAM'"></span> {{ match.winner }}</div>
      <div v-else="" v-t="'NCZONE_MATCH_WINNER_NO_RESULT'"></div>
    </template>

    <div v-t="'NCZONE_MATCH_DRAWER'"></div>
    <div v-html="match.drawer.username"></div>

    <template v-if="match.post_time">
      <div v-t="'NCZONE_MATCH_RESULT_POSTER'"></div>
      <div v-html="match.result_poster.username"></div>
    </template>

    <template v-if="match.map">
      <div v-t="'NCZONE_MATCH_MAP'"></div>
      <div>{{ match.map.title }}</div>
    </template>

    <template v-if="haveGlobalCivs">
      <div v-t="'NCZONE_MATCH_CIVS'"></div>
      <div>
        <div v-if="match.civs.both.length > 0">
          <span v-t="'NCZONE_MATCH_CIVS_BOTH'"></span>: <nczone-civ-list :list="match.civs.both" />
        </div>
        <div v-if="match.civs.team1.length > 0">
          <span v-t="'NCZONE_MATCH_CIVS_TEAM1'"></span>: <nczone-civ-list :list="match.civs.team1" />
        </div>
        <div v-if="match.civs.team2.length > 0">
          <span v-t="'NCZONE_MATCH_CIVS_TEAM2'"></span>:  <nczone-civ-list :list="match.civs.team2" />
        </div>
      </div>
    </template>

    <template v-if="haveBannedCivs && !isFinished">
      <div v-t="'NCZONE_MATCH_CIVS_BANNED'"></div>
      <div>
        <div v-if="match.civs.banned.length > 0">
          <nczone-civ-list :list="match.civs.banned" :tooltip="false" />
        </div>
      </div>
    </template>

    <div v-t="'NCZONE_MATCH_START_TIME'"></div>
    <div>{{ matchStartTime }}</div>

    <template v-if="match.post_time">
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

    <nczone-team :match="match" :team="1" />

    <div class="zone-match-vs" v-t="'NCZONE_MATCH_VS'"></div>

    <nczone-team :match="match" :team="2" />

  </div>

  <div v-if="canAddPairPlayers" class="zone-match-add-pair">
    <button class="zone-button" v-t="'NCZONE_MATCH_ADD_PAIR'" @click="addPair"></button>
  </div>

  <div v-if="canManage && match.post_time === 0" class="zone-match-post-result-form">
    <span v-t="'NCZONE_MATCH_RESULT'"></span>
    <select v-model="matchResult">
      <option v-for="(opt, idx) in matchResultOptions" :key="idx" :value="opt.value" v-t="opt.title"></option>
    </select>
    <button class="zone-button" v-t="'NCZONE_MATCH_SEND_RESULT'" @click="sendResult" :disabled="matchResult === '0'"></button>
  </div>
</div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'
import { pad } from '@/functions'

export default {
  name: 'nczone-match',
  props: {
    match: {
      type: Object,
      required: true
    },
    standalone: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    sendResult () {
      this.postMatchResult({ matchId: this.match.id, winner: this.matchResult })
    },
    cb (now) {
      this.gameSeconds = now / 1000 - this.match.draw_time
    },
    start () {
      this.timer.every(1, this.cb)
    },
    stop () {
      this.timer.off(this.cb)
    },
    addPair () {
      this.openAddPairPreviewOverlay(this.match.id)
    },
    ...mapActions([
      'postMatchResult',
      'openAddPairPreviewOverlay'
    ])
  },
  computed: {
    matchStartTime () {
      const d = new Date(this.match.draw_time * 1000)
      return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear() + ' ' +
        pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds())
    },
    matchLength () {
      const hours = parseInt(this.gameSeconds / 3600, 10)
      const min = parseInt(this.gameSeconds % 3600 / 60, 10)
      const sec = parseInt(this.gameSeconds % 3600 % 60, 10)
      return pad(hours) + ':' + pad(min) + ':' + pad(sec)
    },
    haveGlobalCivs () {
      return this.match.civs.both.length > 0 ||
        this.match.civs.team1.length > 0 ||
        this.match.civs.team2.length > 0
    },
    haveBannedCivs () {
      return this.match.civs.banned.length > 0
    },
    canManage () {
      return this.match.players.team1.map(p => p.id).includes(this.me.id) ||
        this.match.players.team2.map(p => p.id).includes(this.me.id) ||
        this.canModPost
    },
    isFinished () {
      return this.match.post_time > 0
    },
    canAddPairPlayers () {
      return (this.canAddPairMod || (this.canAddPairUser && (this.match.drawer.id === this.me.id))) && !this.isFinished && this.match.players.team1.length < 4
    },
    ...mapGetters([
      'me',
      'timer',
      'canModPost',
      'canAddPairMod',
      'canAddPairUser'
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
    if (!this.match.post_time) {
      this.start()
    } else {
      this.gameSeconds = this.match.post_time - this.match.draw_time
    }
  },
  beforeDestroy () {
    this.stop()
  }
}
</script>
