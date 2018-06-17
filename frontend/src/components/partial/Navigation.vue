<template>
  <div id="zone-navigation">
    <div class="zone-block">
      <div class="zone-title" v-t="'NCZONE_LINKS'"></div>
      <div class="zone-content zone-menu">
        <div v-for="block in blocks" class="zone-menu-block">
          <div v-if="block.title" class="zone-menu-title">{{ block.title }}</div>
          <ul class="zone-menu">
            <li v-for="item in block.items">
              <router-link :to="{name: item.route}">{{ item.title }}</router-link>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  name: 'nczone-navigation',
  data () {
    return {
      title: 'menu',
      blocks: [
        {
          title: this.$t('NCZONE'),
          items: [
            {
              route: 'rmatches',
              title: this.$t('NCZONE_RMATCHES'),
            },
            {
              route: 'pmatches',
              title: this.$t('NCZONE_PMATCHES'),
            },
            {
              route: 'table',
              title: this.$t('NCZONE_TABLE'),
            },
          ]
        },
        {
          title: this.$t('NCZONE_MENU_INFORMATION'),
          items: [
            {
              route: 'rules',
              title: this.$t('NCZONE_RULES'),
            },
          ]
        },
        {
          items: [
            {
              route: 'settings',
              title: this.$t('NCZONE_SETTINGS'),
            },
            {
              // todo: switch language (without routing)
              route: 'switch-language',
              title: this.$t('NCZONE_SWITCH_LANG'),
            },
          ]
        },
      ]
    }
  }
}
</script>
