<template>
  <div v-if="connecting" class="ph-inputSubmitWrapper">
    <div class="ph-loaderWrap ph-inputLoaderWrapper">
      <div class="ph-loaderBox"></div>
      <p class="ph-loaderMessage">Connecting...</p>
    </div>
    <span v-html="error"></span>
  </div>
  <div v-else>
    <button @click="submit" required="required" class="ph-submit ph-adjustmentMl10" type="button">更新<i class="fas fa-sync-alt ph-adjustmentMl5"></i></button>
    <span v-html="error"></span>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue'

  export default Vue.extend({
    props: {
      //親から渡される表示定義
      fields: {
        type: Object,
        required: true
      },

      //このコンポネートが表示される編集画面の項目名
      name: {
        type: String,
        required: true
      },

      //このコンポネートが表示される部分のキー名
      field: {
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
        //現在通信中の場合はtrueとなる
        connecting: false,

        //エラーメッセージ
        errorMessage: ''
      }
    },
    computed: {
      //現在通信中でない場合のみエラーメッセージを返す
      error: function(): string
      {
        return this.connecting ? '' : this.errorMessage;
      }
    },
    methods: {
      submit: function()
      {
        //一旦ボタンをローディングアニメーションにする
        this.connecting = true;

        //変更するデータの用意
        const data: {data: {[key: string]: string}, arguments: [string]} = {data: {}, arguments: [this.$route.params.id]};
        data.data[this.field] = this.data;

        //サーバーサイドに変更を要請
        this.$store.dispatch('connect/connectAPI', {api: 'api/admin/mutation/'+this.name+'/set', data: data})
          .then(() =>
          {
            //エラーメッセージを空文字にする
            this.errorMessage = '';

            //ローディングアニメーションを非表示にする
            this.connecting = false;
          })
          .catch(() =>
          {
            //エラーメッセージを表示
            this.errorMessage = this.$store.state.connect.data.message;

            //ローディングアニメーションを非表示にする
            this.connecting = false;
          });
      }
    }
  });
</script>

<style scoped>

</style>