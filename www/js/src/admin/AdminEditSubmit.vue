<template>
  <div v-if="connecting" class="ph-inputSubmitWrapper">
    <div class="ph-loaderWrap ph-inputLoaderWrapper">
      <div class="ph-loaderBox"></div>
      <p class="ph-loaderMessage">Connecting...</p>
    </div>
  </div>
  <div v-else>
    <button @click="submit" required="required" class="ph-submit ph-adjustmentMl10" type="button">更新<i class="fas fa-sync-alt ph-adjustmentMl5"></i></button>
  </div>
</template>

<script>
  import Vue from 'vue'
  import {AxiosError} from 'axios';

  export default Vue.extend({
    props: {
      //更新する際の通信に使うためのURL
      initApi: {
        type: String,
        required: true,
        default: ''
      },

      //親から渡される表示定義
      fields: {
        type: Object,
        required: true
      },

      //このコンポネートが表示される部分のキー
      name: {
        type: String,
        required: true
      },

      //このコンポーネントが送信する値
      data: {
        type: String,
        required: true
      }
    },
    data () {
      return {
        connecting: false
      }
    },
    methods: {
      submit: function()
      {
        //一旦ボタンをローディングアニメーションにする
        this.connecting = true;

        setTimeout(() =>
        {
          this.connecting = false;
        }, 1000)
      }
    }
  });
</script>

<style scoped>

</style>