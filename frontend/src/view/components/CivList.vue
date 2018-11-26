<template>
    <span>
        <span v-for="(civ, idx) in list" :key="idx">
            <span class="zone-match-civ">{{ $t(civ.title) }}<span class="zone-match-civ-tooltip">{{ civ.multiplier }}</span></span>
            <span v-if="idx+1 < list.length">, </span>
        </span>
    </span>
</template>
<script>
export default {
  name: 'nczone-civ-list',
  props: {
    list: {
      type: Array,
      required: true
    }
  }
}
</script>
