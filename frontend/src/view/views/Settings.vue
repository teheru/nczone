<template>
  <div class="zone-settings">
    <div class="zone-title" v-t="'NCZONE_SETTINGS'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading" v-t="'NCZONE_LOADING'"></div>
      <div class="error" v-else-if="error" v-t="'NCZONE_ERROR_LOADING'"></div>
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
          <button class="zone-button" @click="saveSettings" v-t="'NCZONE_SAVE_SETTINGS'"></button>
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

      loading: true,
      error: false
    }
  },
  async created () {
    await this.syncSettings()
    this.settings = this.me.settings
    this.loading = false
  },
  computed: {
    ...mapGetters([
      'me'
    ])
  },
  methods: {
    async saveSettings () {
      this.loading = true
      await this.setSettings(this.settings)
      await this.syncSettings()
      this.loading = false
    },
    ...mapActions([
      'syncSettings',
      'setSettings'
    ])
  }
}
</script>
