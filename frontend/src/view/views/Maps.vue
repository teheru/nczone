<template>
  <div class="zone-maps">
    <div class="zone-title" v-t="'NCZONE_MAPS'"></div>
    <div class="zone-content">
      <nczone-loading v-if="loading"></nczone-loading>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <template v-else>
        <div class="zone-map-container" v-if="canVeto">
          <div class="zone-map-header">
            <div class="zone-map-veto">
              <span class="zone-map-clear-vetos zone-clickable fa fa-eraser" @click="clearMapVeto()"></span>
              <span class="zone-map-veto-counter fa" v-html="this.mapVetos.vetos_available" tooltip="Vetos Left"></span>
            </div>
          </div>
        </div>
        <div v-for="(map, idx) in weightedMaps" class="zone-map-container" :key="`container-${idx}`">
          <div class="zone-map-header">
            <div class="zone-map-arrow zone-clickable" @click="toggleMap(map.id)">
              <span class="fa" :class="{'fa-angle-right': showMapId !== map.id, 'fa-angle-down': showMapId === map.id}"></span>
            </div>
            <div class="zone-map-name zone-clickable" v-html="map.name" @click="toggleMap(map.id)"></div>
            <div class="zone-map-weight">{{ map.weight }}</div>
            <div class="zone-map-weighted-veto" v-if="'weighted_veto' in map">{{ Math.round(map.weighted_veto * 1000) / 10 }}%</div>
            <div class="zone-map-veto" v-if="canVeto">
              <span v-if="mapVetoLoading == map.id" class="zone-map-veto-loading fa fa-spinner fa-spin"></span>
              <span v-else-if="mapVetos.vetos.includes(map.id)" class="zone-map-veto-enabled zone-clickable fa fa-ban" @click="removeVeto(map.id)"></span>
              <span v-else :class="mapVetos.free_vetos.includes(map.id) ? 'zone-map-veto-free' : 'zone-map-veto-disabled'" class="zone-clickable fa fa-ban" @click="setVeto(map.id)"></span>
            </div>
          </div>
          <div class="zone-map-civs-info" v-if="showMapId === map.id">
            <nczone-map-description v-if="!mapLoading" :map="map" :viewTable="true" />
            <div v-else class="zone-map-civs-table-loading fa fa-spinner fa-spin"></div>
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
      this.mapVetoLoading = mapId
      await this.setMapVeto({ mapId })
      this.mapVetoLoading = 0
    },
    async removeVeto (mapId) {
      this.mapVetoLoading = mapId
      await this.removeMapVeto({ mapId })
      this.mapVetoLoading = 0
    },
    ...mapActions([
      'getMaps',
      'getMapVetos',
      'setMapVeto',
      'removeMapVeto',
      'clearMapVeto',
      'getMapInfo',
      'setSort',
      'openErrorOverlay'
    ])
  },
  computed: {
    weightedMaps () {
      return this.maps.filter(m => m.weight > 0)
    },
    canVeto () {
      return this.mapVetos.vetos.length > 0 || this.mapVetos.vetos_available > 0
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
      mapLoading: false,
      mapVetoLoading: 0
    }
  }
}
</script>
