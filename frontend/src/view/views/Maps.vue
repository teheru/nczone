<template>
  <div class="zone-maps">
    <div class="zone-title" v-t="'NCZONE_MAPS'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <div v-else v-for="(map, idx) in weightedMaps" class="zone-map-container" :key="`container-${idx}`">
        <div class="zone-map-header">
          <div class="zone-map-arrow zone-clickable" @click="toggleMap(map.id)">
            <span class="fa" :class="{'fa-angle-right': showMapId !== map.id, 'fa-angle-down': showMapId === map.id}"></span>
          </div>
          <div class="zone-map-name zone-clickable" v-html="map.name" @click="toggleMap(map.id)"></div>
          <div class="zone-map-weight">{{ map.weight }}</div>
          <div class="zone-map-weighted-veto" v-if="'weighted_veto' in map">{{ Math.round(map.weighted_veto * 10000) / 100 }}%</div>
          <div class="zone-map-veto" v-if="mapVetos.available_vetos > 0">
            <span v-if="mapVetos.vetos.includes(map.id)" class="zone-map-veto-enabled zone-clickable fa fa-ban" @click="removeVeto(map.id)"></span>
            <span v-else class="zone-map-veto-disabled zone-clickable fa fa-ban" @click="setVeto(map.id)"></span>
          </div>
        </div>
        <div class="zone-map-civs-info" v-if="showMapId === map.id">
          <nczone-map-description v-if="!mapLoading" :map="map" :viewTable="true" />
          <div v-else class="zone-map-civs-table-loading fa fa-spinner"></div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-maps',
  created () {
    this.fetchData()
    this.setSort({ field: 'weight', order: -1 })
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    async fetchData () {
      this.loading = true
      try {
        await this.getMaps()
        await this.getMapVetos()
      } catch (error) {
        this.error = true
      } finally {
        this.loading = false
      }
    },
    async toggleMap (mapId) {
      this.showMapId = this.showMapId !== mapId ? mapId : 0
      if (this.showMapId > 0) {
        this.mapLoading = true
        await this.getMapInfo({ mapId: this.showMapId })
        this.mapLoading = false
      }
    },
    async setVeto (mapId) {
      const numberVetos = this.mapVetos.vetos.length
      if (numberVetos < this.mapVetos.available_vetos) {
        await this.setMapVeto({ mapId })
      } else {
        this.openErrorOverlay('NCZONE_ERROR_TOO_MANY_VETOS')
      }
    },
    async removeVeto (mapId) {
      if (this.mapVetos.vetos.includes(mapId)) {
        await this.removeMapVeto({ mapId })
      } else {
        this.openErrorOverlay('NCZONE_ERROR_NO_VETO_FOR_MAP')
      }
    },
    ...mapActions([
      'getMaps',
      'getMapVetos',
      'setMapVeto',
      'removeMapVeto',
      'getMapInfo',
      'setSort',
      'openErrorOverlay'
    ])
  },
  computed: {
    weightedMaps () {
      return this.maps.filter(m => m.weight > 0)
    },
    ...mapGetters([
      'isPlaying',
      'maps',
      'mapVetos'
    ])
  },
  data () {
    return {
      loading: true,
      error: false,
      showMapId: 0,
      mapLoading: false
    }
  }
}
</script>
