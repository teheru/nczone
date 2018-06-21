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
            <li><div @click="toggleLang" v-t="'NCZONE_SWITCH_LANG'"></div></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import Vue from 'vue'
export default {
  name: 'nczone-navigation',
  methods: {
    toggleLang () {
      Vue.config.lang = Vue.config.lang === 'de' ? 'en' : 'de'
      this.$i18n.locale = Vue.config.lang
      localStorage.setItem('nczone-lang', Vue.config.lang)
    }
  }
}
</script>
