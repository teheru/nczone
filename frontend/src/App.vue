<template>
  <div id="nczone" class="zone-container">
    <nczone-navigation />
    <nczone-player-area />

    <div id="zone-main">
      <div class="zone-block">
        <nczone-information />
        <router-view />
      </div>
    </div>

    <nczone-overlay-view />
  </div>
</template>

<script>
export default {
  name: 'app',
  created () {
  },
  data () {
    return {
    }
  }
}
</script>
