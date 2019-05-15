<template>
  <div class="zone-settings">
    <div class="zone-title" v-t="'NCZONE_SETTINGS'"></div>
    <div class="zone-content">
      <div v-if="loading" class="loading" v-t="'NCZONE_LOADING'"></div>
      <div v-else-if="error" class="error" v-t="'NCZONE_ERROR_LOADING'"></div>
      <div v-else>
        <div class="user-settings-table">
          <div class="setting-label">
            <div class="setting-title" v-t="'NCZONE_SETTINGS_VIEW_MCHAT'"></div>
            <div class="setting-description" v-t="'NCZONE_SETTINGS_VIEW_MCHAT_DESCR'"></div>
          </div>
          <div class="setting-value">
            <input type="checkbox" v-model="settings.view_mchat" />
          </div>
        </div>
        <div class="save-settings">
          <button class="zone-button" @click="onSaveClick" v-t="'NCZONE_SAVE_SETTINGS'"></button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapGetters, mapActions } from 'vuex'

export default {
  name: 'nczone-settings',
  data () {
    return {
      settings: {
        view_mchat: true
      },
      loading: false,
      error: false
    }
  },
  async created () {
    this.loading = true
    try {
      await this.loadSettings()
      this.settings = this.me.settings
    } catch (error) {
      this.error = true
    } finally {
      this.loading = false
    }
  },
  computed: {
    ...mapGetters([
      'me'
    ])
  },
  methods: {
    async onSaveClick () {
      this.loading = true
      try {
        await this.saveSettings(this.settings)
        this.settings = this.me.settings
      } catch (error) {
        this.error = true
      } finally {
        this.loading = false
      }
    },
    ...mapActions([
      'loadSettings',
      'saveSettings'
    ])
  }
}
</script>
