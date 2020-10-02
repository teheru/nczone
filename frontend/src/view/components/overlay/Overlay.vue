<template>
  <div class="zone-overlay">
    <div class="zone-overlay-panel">
      <div class="zone-block">
        <div class="zone-title-bar">
          <div class="zone-title" v-t="title"></div>
          <div class="zone-close-button" @click="close">
            <span class="fa fa-times"></span>
          </div>
        </div>
        <div class="zone-content">
          <slot name="content" />
        </div>
        <div v-if="$slots['actions']" class="zone-actions">
          <slot name="actions" class="zone-actions" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'nczone-overlay',
  props: {
    title: {
      type: String,
      required: true
    }
  },
  methods: {
    close () {
      this.$emit('close')
    }
  }
}
</script>
