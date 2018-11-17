<template>
  <div>
    <button v-show="!connecting" @click="submit" required="required" class="ph-submit ph-adjustmentMl10" type="button">更新<i class="fas fa-sync-alt ph-adjustmentMl5"></i></button>
    <div v-show="connecting" class="ph-inputSubmitWrapper">
      <div class="ph-loaderWrap ph-inputLoaderWrapper">
        <div class="ph-loaderBox"></div>
        <p class="ph-loaderMessage">Connecting...</p>
      </div>
    </div>
    <transition name="icon-fade">
      <span v-if="success"><i class="ph-icon fas fa-check-circle"></i></span>
    </transition>
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
        errorMessage: '',

        //更新が成功した場合、一定時間trueになる
        success: false
      }
    },
    computed: {
      //現在通信中でない場合のみエラーメッセージを返す
      error: function(): string
      {
        return this.connecting ? '' : this.errorMessage;
      }
    },
    watch: {
      /**
       * 更新のためにVuexの入力項目データをアップデートする
       * @param value
       */
      data(value: string)
      {
        this.$store.commit('edit/change', {key: this.field, value: value});
      }
    },
    methods: {
      submit: function()
      {
        //一旦ボタンをローディングアニメーションにする
        this.connecting = true;

        //Vuexに変更を要請
        this.$store.commit('edit/queue', {key: this.field});

        //バックエンドと通信する
        this.$store.dispatch('edit/submit')
          .then(() =>
          {
            //エラーメッセージを空文字にする
            this.errorMessage = '';

            //ローディングアニメーションを非表示にする
            this.connecting = false;

            //一定時間successをtrueにする
            this.success = true;
            setTimeout(() =>
            {
              this.success = false;
            }, 700);
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
  .ph-icon{
    font-size: 20px;
    margin-left: 10px;
  }
  .icon-fade-enter-active{
    transition: all .1s ease;
  }
  .icon-fade-leave-active{
    transition: all .5s ease-in-out;
  }
  .icon-fade-enter, .icon-fade-leave-to{
    opacity: 0;
    margin-left: -5px;
  }
</style>