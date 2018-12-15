<template>
    <div class="zone-maps">
        <div class="zone-title" v-t="'NCZONE_MAPS'"></div>
        <div class="zone-content">
          <template v-for="(map, idx) in maps">
            <div class="zone-map-container" v-if="map.weight > 0" :key="`container-${idx}`">
              <div class="zone-map-header" :key="`header-${idx}`">
                <div class="zone-map-name" :key="`name-${idx}`" v-html="map.name"></div>
                <div class="zone-map-weight" :key="`weight-${idx}`">{{ map.weight }}</div>
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
    ...mapActions([
      'getMaps'
    ])
  },
  computed: {
    ...mapGetters([
      'maps'
    ])
  },
  data () {
    return {
      loading: false,
      error: false
    }
  }
}
</script>
