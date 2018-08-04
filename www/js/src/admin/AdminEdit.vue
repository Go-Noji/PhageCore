<template>
  <transition name="eidt">
    <section class="ph-innerWrapper">{{$route.params.id}}</section>
  </transition>
</template>

<script lang="ts">
  import Vue from 'vue'

  export default Vue.extend({
    props: {},
    data () {
      return {
        initApi: {
          type: String,
          required: true,
          default: ''
        }
      }
    },
    watch: {
      '$route' (to, from)
      {
      }
    },
    methods: {
      getData: function()
      {

      }
    }
  });
</script>

<style scoped type="text/scss"></style>