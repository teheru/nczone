<template>
  <span>
    <span v-if="order < 0">▼</span>
    <span v-else>▲</span>
  </span>
</template>
<script>
export default {
  name: 'nczone-sort-indicator',
  props: {
    order: Number,
  },
}
</script>
