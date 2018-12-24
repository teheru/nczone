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
            </div>
            <div class="zone-map-civs-info" v-if="showMapId === mapId">
              <template v-if="!loadMap">
                <div v-if="maps[mapId].description || maps[mapId].image || canEditMapDescription" class="zone-map-description" @dblclick="{{ editDescription() }}">
                  <label v-if="maps[mapId].image || canEditMapDescription" for="upload-map-image" class="zone-map-upload-image-label">
                    <img v-if="maps[mapId].image" class="zone-map-upload-image" :src="maps[mapId].image" />
                    <div v-else-if="canEditMapDescription" class="zone-map-upload-image">{{ $t('NCZONE_UPLOAD_IMAGE') }}</div>
                    <div v-if="canEditMapDescription" class="zone-map-upload-image-description">{{ $t('NCZONE_UPLOAD_IMAGE_HINT') }}</div>
                  </label>
                  <input id="upload-map-image" v-if="canEditMapDescription" type="file" accept="image/*" @change="uploadImage" />
                  <template v-if="editDescr && canEditMapDescription">
                    <textarea v-model="tempDescription" rows="10"></textarea><br />
                    <button @click="{{ saveDescription() }}">{{ $t('NCZONE_SAVE') }}</button>
                  </template>
                  <vue-markdown v-else-if="maps[mapId].description || canEditMapDescription" class="zone-map-description-text">{{ maps[mapId].description ? maps[mapId].description : '*' + $t('NCZONE_EMPTY_DESCRIPTION') + '*' }}</vue-markdown>
                </div>
                <div class="zone-map-civs-table">
                  <div class="zone-map-civs-table-head">
                    <div class="zone-map-civs-table-head-name">{{ $t('NCZONE_CIV') }}</div>
                    <div class="zone-map-civs-table-head-multiplier">{{ $t('NCZONE_MULTIPLIER') }}</div>
                    <div class="zone-map-civs-table-head-force-draw">{{ $t('NCZONE_FORCE_DRAW') }}</div>
                    <div class="zone-map-civs-table-head-prevent-draw">{{ $t('NCZONE_PREVENT_DRAW') }}</div>
                    <div class="zone-map-civs-table-head-both-teams">{{ $t('NCZONE_BOTH_TEAMS') }}</div>
                  </div>
                  <div class="zone-map-civs-table-row" v-for="(civ, idy) in maps[mapId].civInfo" :key="`row-${idy}`">
                    <div class="zone-map-civs-table-name" :key="`name-${idy}`">{{ $t(civ.civ_name) }}</div>
                    <div class="zone-map-civs-table-multiplier" :key="`multiplier-${idy}`">{{ civ.multiplier }}</div>
                    <div class="zone-map-civs-table-force-draw fa fa-check" :key="`force-draw-${idy}`" v-if="civ.force_draw"></div>
                    <div class="zone-map-civs-table-force-draw fa fa-times" :key="`force-draw-${idy}`" v-else=""></div>
                    <div class="zone-map-civs-table-prevent-draw fa fa-check" :key="`prevent-draw-${idy}`" v-if="civ.prevent_draw"></div>
                    <div class="zone-map-civs-table-prevent-draw fa fa-times" :key="`prevent-draw-${idy}`" v-else=""></div>
                    <div class="zone-map-civs-table-both-teams fa fa-check" :key="`both-teams-${idy}`" v-if="civ.both_teams"></div>
                    <div class="zone-map-civs-table-both-teams fa fa-times" :key="`both-teams-${idy}`" v-else=""></div>
                  </div>
                </div>
              </template>
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
import VueMarkdown from 'vue-markdown'

export default {
  name: 'nczone-maps',
  components: {
    VueMarkdown
  },
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
      this.loadMap = true
      this.editDescr = false
      const toggleId = mapId !== this.showMapId ? mapId : 0
      if (toggleId) {
        await this.getMapInfo({ id: toggleId })
      }
      this.showMapId = toggleId
      this.loadMap = false
    },

    editDescription () {
      if (this.canEditMapDescription) {
        this.tempDescription = this.maps[this.showMapId].description
        this.editDescr = true
      }
    },

    async saveDescription () {
      this.loadMap = true
      await this.saveMapDescription({ id: this.showMapId, description: this.tempDescription })
      this.editDescr = false
      this.loadMap = false
    },

    async uploadImage (evt) {
      this.loadMap = true
      var files = evt.target.files || evt.dataTransfer.files
      if (!files.length) {
        return
      }
      var reader = new FileReader();
      reader.onload = async (p) => {
        await this.saveMapImage({ id: this.showMapId, image: p.target.result })
        this.loadMap = false
      }
      reader.readAsDataURL(files[0])
    },

    ...mapActions([
      'getMaps',
      'getMapInfo',
      'saveMapDescription',
      'saveMapImage'
    ])
  },
  computed: {
    ...mapGetters([
      'maps',
      'canEditMapDescription'
    ])
  },
  data () {
    return {
      loading: true,
      error: false,
      orderedMapIds: [],
      showMapId: 0,
      loadMap: true,
      editDescr: false,
      tempDescription: ''
    }
  }
}
</script>
