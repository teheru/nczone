<template>
  <span><span v-for="(item, idx) in list">{{ item }}<span v-if="idx+1 < list.length">, </span></span></span>
</template>
<script>
export default {
  name: 'nczone-csv',
  props: {
    list: {
      type: Array,
      required: true,
    },
  }
}
</script>
