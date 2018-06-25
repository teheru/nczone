<template>
  <div id="zone-navigation">
    <div class="zone-block">
      <div class="zone-title" v-t="'NCZONE_LINKS'"></div>
      <div class="zone-content zone-menu">
        <div class="zone-menu-block">
          <div class="zone-menu-title" v-t="'NCZONE'"></div>
          <ul class="zone-menu">
            <li><router-link :to="{name: 'rmatches'}" v-t="'NCZONE_RMATCHES'"></router-link></li>
            <li><router-link :to="{name: 'pmatches'}" v-t="'NCZONE_PMATCHES'"></router-link></li>
            <li><router-link :to="{name: 'players'}" v-t="'NCZONE_PLAYERS_TABLE'"></router-link></li>
          </ul>
        </div>
        <div class="zone-menu-block">
          <div class="zone-menu-title" v-t="'NCZONE_MENU_INFORMATION'"></div>
          <ul class="zone-menu">
            <li><router-link :to="{name: 'rules'}" v-t="'NCZONE_RULES'"></router-link></li>
          </ul>
        </div>

        <div class="zone-menu-block">
          <ul class="zone-menu">
            <li><router-link :to="{name: 'settings'}" v-t="'NCZONE_SETTINGS'"></router-link></li>
            <li><a @click="toggleLang" v-t="'NCZONE_SWITCH_LANG'"></a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'nczone-navigation',
  methods: {
    async toggleLang () {
      this.$store.dispatch('toggleLanguage')
    }
  }
}
</script>
