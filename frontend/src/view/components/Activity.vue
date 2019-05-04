<template>
  <span class="zone-activity" :title="activity">
    <span v-for="n in 5" class="zone-activity-bar" :class="{'active': n <= activity}"></span>
  </span>
</template>
<script>
export default {
  name: 'nczone-activiy',
  props: {
    activity: Number,
  },
}
</script>
