<template>
  <div>
    <div v-if="map.description || map.image || canEditMapDescription" class="zone-map-description" @dblclick="editDescription()">
      <label v-if="map.image || canEditMapDescription" for="upload-map-image" class="zone-map-upload-image-label">
        <img v-if="map.image" class="zone-map-upload-image" :src="map.image" />
        <div v-else-if="canEditMapDescription" class="zone-map-upload-image" v-t="'NCZONE_UPLOAD_IMAGE'"></div>
        <div v-if="canEditMapDescription" class="zone-map-upload-image-description" v-t="'NCZONE_UPLOAD_IMAGE_HINT'"></div>
      </label>
      <input id="upload-map-image" v-if="canEditMapDescription" type="file" accept="image/*" @change="uploadImage" />
      <template v-if="editDescr && canEditMapDescription">
        <textarea v-model="tempDescription" rows="10"></textarea><br />
        <button @click="saveDescription()" v-t="'NCZONE_SAVE'"></button>
      </template>
      <vue-markdown v-else-if="map.description || canEditMapDescription" class="zone-map-description-text">{{ map.description ? map.description : '*' + $t('NCZONE_EMPTY_DESCRIPTION') + '*' }}</vue-markdown>
    </div>
    <div class="zone-map-civs-table" v-if="viewTable">
      <div class="zone-table-row zone-table-head-row">
        <nczone-table-header-col label="NCZONE_CIV" sort-field="civname" />
        <nczone-table-header-col label="NCZONE_MULTIPLIER" sort-field="multiplier" />
        <nczone-table-header-col label="NCZONE_FORCE_DRAW" sort-field="force_draw" />
        <nczone-table-header-col label="NCZONE_PREVENT_DRAW" sort-field="prevent_draw" />
        <nczone-table-header-col label="NCZONE_BOTH_TEAMS" sort-field="both_teams" />
      </div>
      <div class="zone-table-row" v-for="(civ, idx) in currentCivInfos" :key="`row-${idx}`">
        <div>{{ $t(civ.civ_name) }}</div>
        <div>{{ civ.multiplier }}</div>
        <div class="fa" :class="{'fa-check': civ.force_draw, 'fa-times': !civ.force_draw}"></div>
        <div class="fa" :class="{'fa-check': civ.prevent_draw, 'fa-times': !civ.prevent_draw}"></div>
        <div class="fa" :class="{'fa-check': civ.both_teams, 'fa-times': !civ.both_teams}"></div>
      </div>
    </div>
  </div>
</template>
<script>
import VueMarkdown from 'vue-markdown'

import { mapGetters, mapActions } from 'vuex'

import { sort } from '@/functions'

export default {
  name: 'nczone-map-description',
  created () {
    this.setSort({ field: 'civname', order: -1 })
  },
  components: {
    VueMarkdown
  },
  props: {
    map: Object,
    viewTable: Boolean
  },
  watch: {
    '$route': 'fetchData'
  },
  methods: {
    editDescription () {
      if (this.canEditMapDescription) {
        this.tempDescription = this.map.description
        this.editDescr = true
      }
    },

    async saveDescription () {
      this.loading = true
      await this.saveMapDescription({ mapId: this.map.id, description: this.tempDescription })
      this.editDescr = false
      this.loading = false
    },

    async uploadImage (evt) {
      this.loading = true
      const files = evt.target.files || evt.dataTransfer.files
      if (!files.length) {
        return
      }
      const reader = new FileReader()
      reader.onload = async (p) => {
        await this.saveMapImage({ mapId: this.map.id, image: p.target.result })
        this.loading = false
      }
      reader.readAsDataURL(files[0])
    },
    ...mapActions([
      'setSort',
      'saveMapDescription',
      'saveMapImage'
    ])
  },
  computed: {
    currentCivInfos () {
      return sort(this.map.civInfo.filter(c => c.multiplier > 0), this.sort)
    },

    ...mapGetters([
      'canEditMapDescription',
      'sort'
    ])
  },
  data () {
    return {
      editDescr: false,
      tempDescription: ''
    }
  }
}
</script>
