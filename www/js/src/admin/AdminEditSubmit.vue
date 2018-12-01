<template>
  <div>
    <button v-show=" ! isConnecting" @click="submit" required="required" class="ph-submit ph-adjustmentMl10" type="button">更新<i class="fas fa-sync-alt ph-adjustmentMl5"></i></button>
    <div v-show="isConnecting" class="ph-inputSubmitWrapper">
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
      },

      //他全ての項目が更新中の場合にtrueとなるフラグ
      connectAll: {
        type: Boolean,
        required: true
      }
    },
    data () {
      return {
        //現在通信中の場合はtrueとなる
        connecting: false,

        //ローディングアニメーション表示に使うSetTimeoutの返り値
        timer: 0,

        //更新が成功した場合、一定時間trueになる
        success: false
      }
    },
    computed: {
      //現在通信中でない場合のみエラーメッセージを返す
      error: function(): string
      {
        return ! this.connecting && ! this.$store.state.edit.data[this.field].success
          ? this.$store.state.edit.data[this.field].error
          : '';
      },

      //通信中だった場合は0.3秒後にローディングアニメーションを表示する関数を登録、
      //そうでなかった場合はその関数をキャンセルする
      isConnecting: function ()
      {
        this.success = false;
        if (this.$store.state.edit.data[this.field].connect)
        {
          this.showLoading();
        }
        else if(this.timer)
        {
          this.hiddenLoading();

          //もしエラーがなければ一定時間成功表示を出す
          if (this.$store.state.edit.data[this.field].success)
          {
            this.showSuccess();
          }
        }
        return this.connecting;
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
      /**
       * ローディングアニメーションを表示するためにSetTimeoutを登録する
       */
      showLoading: function()
      {
        //一旦ボタンをローディングアニメーションにする
        //0.3秒後待ってその間にサーバーサイドから応答があったらキャンセルする
        this.timer = setTimeout(() =>
        {
          this.connecting = true;
        }, 300);
      },

      /**
       * ローディングアニメーションを非表示にし、showLoading()のタイマーもclearTimeoutする
       */
      hiddenLoading: function()
      {
        //ローディングアニメーション表示タイマーのキャンセル
        clearTimeout(this.timer);

        //ローディングアニメーションを非表示にする
        this.connecting = false;
      },

      /**
       * 一定時間成功表示を出す
       */
      showSuccess: function()
      {
        this.success = true;
        setTimeout(() =>
        {
          this.success = false;
        }, 700);
      },

      /**
       * 更新を実行する
       */
      submit: function()
      {
        //ローディングアニメーションの表示
        this.showLoading();

        //Vuexに変更を要請
        this.$store.commit('edit/queue', {key: this.field});

        //バックエンドと通信する
        this.$store.dispatch('edit/submit')
          .then(() =>
          {
            //ローディングアニメーションの非表示
            this.hiddenLoading();

            //一定時間成功表示を出す
            this.showSuccess();
          })
          .catch(() =>
          {
            //ローディングアニメーションの非表示
            this.hiddenLoading();
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