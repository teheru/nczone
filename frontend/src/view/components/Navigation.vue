<template>
  <div id="zone-navigation">
    <div class="zone-block">
      <div class="zone-title" v-t="'NCZONE_LINKS'"></div>
      <div class="zone-content zone-menu">
        <div class="zone-menu-block">
          <div class="zone-menu-title" v-t="'NCZONE'"></div>
          <ul class="zone-menu">
            <li><router-link :to="{name: 'rmatches'}" v-t="'NCZONE_RMATCHES'" /></li>
            <li><router-link :to="{name: 'pmatches'}" v-t="'NCZONE_PMATCHES'" /></li>
            <li><router-link :to="{name: 'players'}" v-t="'NCZONE_PLAYERS_TABLE'" /></li>
            <li><router-link :to="{name: 'bets'}" v-t="'NCZONE_BETS'" /></li>
            <!-- <li><router-link :to="{name: 'statistics'}" v-t="'NCZONE_STATISTICS'"></router-link></li> -->
          </ul>
        </div>
        <div class="zone-menu-block">
          <div class="zone-menu-title" v-t="'NCZONE_MENU_INFORMATION'"></div>
          <ul class="zone-menu">
            <li><router-link :to="{name: 'rules'}" v-t="'NCZONE_RULES'" /></li>
          </ul>
        </div>

        <div class="zone-menu-block">
          <ul class="zone-menu">
            <li><router-link :to="{name: 'settings'}" v-t="'NCZONE_SETTINGS'" /></li>
            <li><a @click="toggleLang" v-t="'NCZONE_SWITCH_LANG'"></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapActions } from 'vuex'
export default {
  name: 'nczone-navigation',
  methods: {
    async toggleLang () {
      this.toggleLanguage()
    },
    ...mapActions([
      'toggleLanguage'
    ])
  }
}
</script>
