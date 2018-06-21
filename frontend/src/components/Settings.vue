<template>
  <div class="zone-settings">
    <div class="zone-title" v-t="'NCZONE_SETTINGS'"></div>
    <div class="zone-content">
      <div class="loading" v-if="loading" v-t="'NCZONE_LOADING'"></div>
      <div class="error" v-else-if="error" v-t="'NCZONE_ERROR_LOADING'"></div>
      <template v-else="">
        <div></div>
      </template>
    </div>
  </div>
</template>
<script>
export default {
  name: 'nczone-settings'
}
</script>
