<template>
  <div id="nczone" class="zone-container">
    <nczone-navigation></nczone-navigation>
    <nczone-player-area></nczone-player-area>

    <div id="zone-main">
      <div class="zone-block">
        <nczone-information></nczone-information>

        <router-view></router-view>
      </div>
    </div>

    <nczone-draw-preview></nczone-draw-preview>
  </div>
</template>

<script>
import NczoneNavigation from './components/partial/Navigation'
import NczonePlayerArea from './components/partial/PlayerArea'
import NczoneDrawPreview from './components/partial/DrawPreview'
import NczoneInformation from './components/Information'

export default {
  name: 'app',
  components: {
    NczoneInformation,
    NczoneDrawPreview,
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
