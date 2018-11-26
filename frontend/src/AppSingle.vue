<template>
  <div v-if="match">
    <nczone-match :match="match" />
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'app-single',
  computed: {
    ...mapGetters([
      'match'
    ])
  }
}
</script>
