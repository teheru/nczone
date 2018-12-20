<template>
  <div class="zone-maps">
    <div class="zone-title" v-t="'NCZONE_MAPS'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <template v-else="" v-for="(map, idx) in maps">
        <div class="zone-map-container" v-if="map.weight > 0" :key="`container-${idx}`">
          <div class="zone-map-header" :key="`header-${idx}`">
            <div class="zone-map-arrow" :key="`arrow-${idx}`" @click="{{ toggleMap(map.id) }}">
              <span class="fa fa-angle-right" v-if="mapInfo.id !== map.id"></span>
              <span class="fa fa-angle-down" v-if="mapInfo.id === map.id"></span>
            </div>
            <div class="zone-map-name" :key="`name-${idx}`" v-html="map.name"></div>
            <div class="zone-map-weight" :key="`weight-${idx}`">{{ map.weight }}</div>
          </div>
          <div class="zone-map-civs-info" v-if="mapInfo.id === map.id">
            <div v-if="!loadMap" class="zone-map-civs-table">
              <div class="zone-map-civs-table-head">{{ $t('NCZONE_CIV') }}</div>
              <div class="zone-map-civs-table-head">{{ $t('NCZONE_MULTIPLIER') }}</div>
              <div class="zone-map-civs-table-head">{{ $t('NCZONE_FORCE_DRAW') }}</div>
              <div class="zone-map-civs-table-head">{{ $t('NCZONE_PREVENT_DRAW') }}</div>
              <div class="zone-map-civs-table-head">{{ $t('NCZONE_BOTH_TEAMS') }}</div>
            <template v-for="(civ, idy) in mapInfo.civInfo">
              <div class="zone-map-civs-table-name" :class="{'even': idy % 2 === 0, 'odd': idy % 2 !== 0}" :key="`name-${idy}`">{{ $t(civ.civ_name) }}</div>
              <div class="zone-map-civs-table-multiplier" :class="{'even': idy % 2 === 0, 'odd': idy % 2 !== 0}" :key="`multiplier-${idy}`">{{ civ.multiplier }}</div>
              <div class="zone-map-civs-table-force-draw fa fa-check" :class="{'even': idy % 2 === 0, 'odd': idy % 2 !== 0}" :key="`force-draw-${idy}`" v-if="civ.force_draw"></div>
              <div class="zone-map-civs-table-force-draw fa fa-times" :class="{'even': idy % 2 === 0, 'odd': idy % 2 !== 0}" :key="`force-draw-${idy}`" v-else=""></div>
              <div class="zone-map-civs-table-prevent-draw fa fa-check" :class="{'even': idy % 2 === 0, 'odd': idy % 2 !== 0}" :key="`prevent-draw-${idy}`" v-if="civ.prevent_draw"></div>
              <div class="zone-map-civs-table-prevent-draw fa fa-times" :class="{'even': idy % 2 === 0, 'odd': idy % 2 !== 0}" :key="`prevent-draw-${idy}`" v-else=""></div>
              <div class="zone-map-civs-table-both-teams fa fa-check" :class="{'even': idy % 2 === 0, 'odd': idy % 2 !== 0}" :key="`both-teams-${idy}`" v-if="civ.both_teams"></div>
              <div class="zone-map-civs-table-both-teams fa fa-times" :class="{'even': idy % 2 === 0, 'odd': idy % 2 !== 0}" :key="`both-teams-${idy}`" v-else=""></div>
            </template>
            </div>
            <div class="zone-map-civs-table-loading fa fa-spinner" v-else=""></div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-maps',
  created () {
    this.fetchData()
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    async fetchData () {
      this.loading = true
      try {
        await this.getMaps()
      } catch (error) {
        this.error = true
      } finally {
        this.loading = false
      }
    },

    async toggleMap(mapId) {
      this.loadMap = true
      if (mapId != this.mapInfo.id) {
        await this.getMapInfo({id: 0})
        await this.getMapInfo({id: mapId})
      } else {
        await this.getMapInfo({id: 0})
      }
      this.loadMap = false
    },

    ...mapActions([
      'getMaps',
      'getMapInfo'
    ])
  },
  computed: {
    ...mapGetters([
      'maps',
      'mapInfo'
    ])
  },
  data () {
    return {
      loading: false,
      error: false,
      loadMap: true
    }
  }
}
</script>
