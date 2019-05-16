<template>
  <div class="zone-maps">
    <div class="zone-title" v-t="'NCZONE_MAPS'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <div v-else v-for="(map, idx) in weightedMaps" class="zone-map-container" :key="`container-${idx}`">
        <div class="zone-map-header zone-clickable" @click="toggleMap(map.id)">
          <div class="zone-map-arrow">
            <span class="fa" :class="{'fa-angle-right': showMapId !== map.id, 'fa-angle-down': showMapId === map.id}"></span>
          </div>
          <div class="zone-map-name" v-html="map.name"></div>
          <div class="zone-map-weight">{{ map.weight }}</div>
          <div class="zone-map-percent">{{ Math.round(map.proportion * 10000) / 100 }}%</div>
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
    ...mapActions([
      'getMaps',
      'getMapInfo',
      'setSort'
    ])
  },
  computed: {
    weightedMaps () {
      return this.maps.filter(m => m.weight > 0)
    },
    ...mapGetters([
      'maps'
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
