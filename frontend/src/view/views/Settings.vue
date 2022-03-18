<template>
  <div class="zone-settings">
    <div class="zone-title" v-t="'NCZONE_SETTINGS'"></div>
    <div class="zone-content">
      <nczone-loading v-if="loading"></nczone-loading>
      <div v-else-if="error" class="error" v-t="'NCZONE_ERROR_LOADING'"></div>
      <div v-else>
        <div class="user-settings-table">

          <div class="setting-label">
            <div class="setting-title" v-t="'NCZONE_SETTINGS_VIEW_MCHAT'"></div>
            <div class="setting-description" v-t="'NCZONE_SETTINGS_VIEW_MCHAT_DESCR'"></div>
          </div>
          <div class="setting-value">
            <input class="view_mchat" type="checkbox" v-model="settings.view_mchat" />
          </div>

          <div class="setting-label">
            <div class="setting-title" v-t="'NCZONE_SETTINGS_AUTO_LOGOUT'"></div>
            <div class="setting-description" v-t="'NCZONE_SETTINGS_AUTO_LOGOUT_DESCR'"></div>
          </div>
          <div class="setting-value">
            <input class="auto_logout" type="number" min="0" max="999" step="1" v-model.number="settings.auto_logout" />
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
        view_mchat: true,
        auto_logout: 0
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
