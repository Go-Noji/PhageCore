<template>
  <li class="pc-adminSidebarList">
    <router-link class="pc-adminSidebarLink pc-btnLink" :to="to">
      <i class="pc-icon fas" :class="icon"></i>
      <span class="pc-reverseColor">{{title}}</span>
    </router-link>
  </li>
</template>

<script lang="ts">
  import Vue from 'vue'

  //CodeIgniterが提供する変数
  declare var site_url: string;

  export default Vue.extend({
    props: {
      icon: {
        type: String,
        required: true
      },
      title: {
        type: String,
        required: true
      },
      to: {
        type: String,
        required: true
      }
    },
    data () {
      return {
        contents: {},
        fields: {}
      }
    },
    watch: {},
    mounted: function() {

    },
    methods: {

    }
  });
</script>

<style scoped></style>