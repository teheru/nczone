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
                  <span class="fas fa-angle-right" v-if="mapInfo.id != map.id"></span>
                  <span class="fas fa-angle-down" v-if="mapInfo.id == map.id"></span>
                </div>
                <div class="zone-map-name" :key="`name-${idx}`" v-html="map.name"></div>
                <div class="zone-map-weight" :key="`weight-${idx}`">{{ map.weight }}</div>
              </div>
              <div class="zone-map-civs-info" v-if="mapInfo.id == map.id">
                <br />
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
      this.loadMap = mapId
      if (mapId != this.mapInfo.id) {
        this.getMapInfo({id: 0})
        this.getMapInfo({id: mapId})
      } else {
        this.getMapInfo({id: 0})
      }
      this.loadMap = 0
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
      loadMap: 0
    }
  }
}
</script>
