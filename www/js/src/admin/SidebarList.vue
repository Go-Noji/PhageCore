<template>
  <li class="ph-adminSidebarList">
    <router-link class="ph-adminSidebarLink ph-btnLink" :to="to">
      <i class="ph-icon fas" :class="icon"></i>
      <span class="ph-reverseColor">{{title}}</span>
    </router-link>
  </li>
</template>

<script lang="ts">
  import Vue from 'vue'

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
    }
  });
</script>

<style scoped></style>