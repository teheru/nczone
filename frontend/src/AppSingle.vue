<template>
  <div v-if="match">
    <nczone-match :match="match"></nczone-match>
  </div>
</template>

<script>
import NczoneMatch from './components/partial/Match'
import {mapGetters} from 'vuex'

export default {
  name: 'app-single',
  components: {
    NczoneMatch
  },
  computed: {
    ...mapGetters([
      'match'
    ])
  }
}
</script>
