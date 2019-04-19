<template>
  <div class="zone-maps">
    <div class="zone-title" v-t="'NCZONE_MAPS'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading"><span v-t="'NCZONE_LOADING'"></span></div>
      <div class="error" v-else-if="error"><span v-t="'NCZONE_ERROR_LOADING'"></span></div>
      <template v-else>
        <template v-for="mapId in orderedMapIds">
          <div class="zone-map-container" v-if="maps[mapId].weight > 0" :key="`container-${mapId}`">
            <div class="zone-map-header" :key="`header-${mapId}`">
              <div class="zone-map-arrow" :key="`arrow-${mapId}`" @click="{{ toggleMap(mapId) }}">
                <span class="fa fa-angle-right" v-if="showMapId !== mapId"></span>
                <span class="fa fa-angle-down" v-if="showMapId === mapId"></span>
              </div>
              <div class="zone-map-name" :key="`name-${mapId}`" v-html="maps[mapId].name"></div>
              <div class="zone-map-weight" :key="`weight-${mapId}`">{{ maps[mapId].weight }}</div>
              <div class="zone-map-percent" :key="`percent-${mapId}`">{{ Math.round(maps[mapId].proportion * 10000) / 100 }}%</div>
            </div>
            <div class="zone-map-civs-info" v-if="showMapId === mapId">
              <nczone-map-description :map="maps[showMapId]" :viewTable="true" v-if="!mapLoading"></nczone-map-description>
              <div class="zone-map-civs-table-loading fa fa-spinner" v-else></div>
            </div>
          </div>
        </template>
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
        this.orderMapIds()
      } catch (error) {
        this.error = true
      } finally {
        this.loading = false
      }
    },

    orderMapIds () {
      this.orderedMapIds = Object.keys(this.maps).sort((a, b) => {
        var mapA = this.maps[a]
        var mapB = this.maps[b]
        if (mapA.weight === mapB.weight) {
          return 0
        }
        return mapA.weight < mapB.weight ? 1 : -1
      })
    },

    async toggleMap (mapId) {
      this.showMapId = this.showMapId !== mapId ? mapId : 0
      this.mapLoading = true
      await this.getMapInfo({ id: this.showMapId })
      this.mapLoading = false
    },

    ...mapActions([
      'getMaps',
      'getMapInfo'
    ])
  },
  computed: {
    ...mapGetters([
      'maps'
    ])
  },
  data () {
    return {
      loading: true,
      error: false,
      orderedMapIds: [],
      showMapId: 0,
      mapLoading: false
    }
  }
}
</script>
