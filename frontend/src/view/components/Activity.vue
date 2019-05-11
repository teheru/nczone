<template>
  <span class="zone-activity" :title="activity_matches">
    <span v-for="n in 5" class="zone-activity-bar" :class="{'active': n <= activity}" :key="`activity-${n}`"></span>
  </span>
</template>
<script>
export default {
  name: 'nczone-activity',
  props: {
    activity: Number,
    activity_matches: Number
  }
}
</script>
