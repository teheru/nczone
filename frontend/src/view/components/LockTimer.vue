<template>
  <span class="zone-small">(<span v-html="lockLength"></span>)</span>
</template>
<script>
import { renderTime } from '@/functions'

export default {
  name: 'nczone-lock-timer',
  props: {
    lockLengthSeconds: {
      type: Number,
      required: true
    }
  },
  computed: {
    lockLength () {
      return renderTime(this.lockLengthSeconds, false)
    }
  }
}
</script>
