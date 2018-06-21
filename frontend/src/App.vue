<template>
  <div id="nczone" class="zone-container">
    <nczone-navigation></nczone-navigation>
    <nczone-player-area></nczone-player-area>

    <div id="zone-main">
      <div class="zone-block">
        <div id="zone_infos">
          <div class="zone-title" v-t="'NCZONE_INFORMATION'"></div>
          <div class="zone-content">
          </div>
        </div>

        <router-view></router-view>
      </div>
    </div>
  </div>
</template>

<script>
import NczoneNavigation from './components/partial/Navigation'
import NczonePlayerArea from './components/partial/PlayerArea'

export default {
  name: 'app',
  components: {
    NczonePlayerArea,
    NczoneNavigation
  },
  created () {
  },
  data () {
    return {
    }
  }
}
</script>
