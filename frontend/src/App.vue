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

    <nczone-player-details></nczone-player-details>

    <nczone-draw-preview></nczone-draw-preview>

    <nczone-replace-preview></nczone-replace-preview>

    <nczone-add-pair-preview></nczone-add-pair-preview>
  </div>
</template>

<script>
import NczoneNavigation from './components/partial/Navigation'
import NczonePlayerArea from './components/partial/PlayerArea'
import NczonePlayerDetails from './components/partial/PlayerDetails'
import NczoneDrawPreview from './components/partial/DrawPreview'
import NczoneReplacePreview from './components/partial/ReplacePreview'
import NczoneAddPairPreview from './components/partial/AddPairPreview'
import NczoneInformation from './components/Information'

export default {
  name: 'app',
  components: {
    NczoneInformation,
    NczonePlayerDetails,
    NczoneDrawPreview,
    NczoneReplacePreview,
    NczoneAddPairPreview,
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
