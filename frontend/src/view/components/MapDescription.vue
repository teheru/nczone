<template>
  <div v-if="!loading">
    <div v-if="map.description || map.image || canEditMapDescription" class="zone-map-description" @dblclick="editDescription()">
      <label v-if="map.image || canEditMapDescription" for="upload-map-image" class="zone-map-upload-image-label">
        <img v-if="map.image" class="zone-map-upload-image" :src="map.image" />
        <div v-else-if="canEditMapDescription" class="zone-map-upload-image">{{ $t('NCZONE_UPLOAD_IMAGE') }}</div>
        <div v-if="canEditMapDescription" class="zone-map-upload-image-description">{{ $t('NCZONE_UPLOAD_IMAGE_HINT') }}</div>
      </label>
      <input id="upload-map-image" v-if="canEditMapDescription" type="file" accept="image/*" @change="uploadImage" />
      <template v-if="editDescr && canEditMapDescription">
        <textarea v-model="tempDescription" rows="10"></textarea><br />
        <button @click="saveDescription()">{{ $t('NCZONE_SAVE') }}</button>
      </template>
      <vue-markdown v-else-if="map.description || canEditMapDescription" class="zone-map-description-text">{{ map.description ? map.description : '*' + $t('NCZONE_EMPTY_DESCRIPTION') + '*' }}</vue-markdown>
    </div>
    <div class="zone-map-civs-table">
      <div class="zone-map-civs-table-head">
        <div class="zone-map-civs-table-head-name">{{ $t('NCZONE_CIV') }}</div>
        <div class="zone-map-civs-table-head-multiplier">{{ $t('NCZONE_MULTIPLIER') }}</div>
        <div class="zone-map-civs-table-head-force-draw">{{ $t('NCZONE_FORCE_DRAW') }}</div>
        <div class="zone-map-civs-table-head-prevent-draw">{{ $t('NCZONE_PREVENT_DRAW') }}</div>
        <div class="zone-map-civs-table-head-both-teams">{{ $t('NCZONE_BOTH_TEAMS') }}</div>
      </div>
      <div class="zone-map-civs-table-row" v-for="(civ, idy) in map.civInfo" :key="`row-${idy}`">
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
  </div>
  <div class="zone-map-civs-table-loading fa fa-spinner" v-else></div>
</template>
<script>
import VueMarkdown from 'vue-markdown'

import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-map-description',
  components: {
    VueMarkdown
  },
  props: [
    'mapId',
    'map'
  ],
  created () {
    this.fetchData()
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    async fetchData () {
      this.loading = true
      this.editDescr = false
      await this.getMapInfo({ id: this.mapId })
      this.loading = false
    },

    editDescription () {
      if (this.canEditMapDescription) {
        this.tempDescription = this.map.description
        this.editDescr = true
      }
    },

    async saveDescription () {
      this.loading = true
      await this.saveMapDescription({ id: this.mapId, description: this.tempDescription })
      this.editDescr = false
      this.loading = false
    },

    async uploadImage (evt) {
      this.loading = true
      var files = evt.target.files || evt.dataTransfer.files
      if (!files.length) {
        return
      }
      var reader = new FileReader()
      reader.onload = async (p) => {
        await this.saveMapImage({ id: this.mapId, image: p.target.result })
        this.loading = false
      }
      reader.readAsDataURL(files[0])
    },

    ...mapActions([
      'getMapInfo',
      'saveMapDescription',
      'saveMapImage'
    ])
  },
  computed: {
    ...mapGetters([
      'canEditMapDescription'
    ])
  },
  data () {
    return {
      loading: true,
      error: false,
      editDescr: false,
      tempDescription: ''
    }
  }
}
</script>
