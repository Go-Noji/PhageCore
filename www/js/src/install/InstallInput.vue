<template>
  <div>
    <div>
      <h3 class="pc-subTitle">{{title}}</h3>
      <p :class="messageClass">{{formMessage}}</p>
    </div>
    <div>
      <input :id="id" :name="name" :value="formValue" :placeholder="placeholder" :class="formClass" v-model="formValue" :type="type">
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue'
  import axios, {CancelTokenSource} from 'axios';

  //CodeIgniterが提供する変数
  declare var csrf_key: string;
  declare var csrf_value: string;
  declare var site_url: string;

  //バリデーションを行ったときに返ってくるデータ
  //validationにバリデーションのエラーメッセージが入る
  interface ValidationData{
    validation: {[key: string]: string}
  }

  //デフォルトのメッセージクラス
  const defaultMessageClass: string = 'pc-paragraph pc-message';

  //通信中のaxiosキャンセルトークン
  let source: CancelTokenSource|null = null;

  export default Vue.extend({
    props: {
      name: {
        type: String,
        required: true,
        default: ''
      },
      placeholder: {
        type: String,
        required: true,
        default: ''
      },
      message: {
        type: String,
        required: true,
        default: ''
      },
      title: {
        type: String,
        required: true,
        default: ''
      },
      type: {
        type: String,
        required: false,
        default: 'text'
      },
      value: {
        type: String,
        required: false,
        default: ''
      },
      include: {
        type: String,
        required: false,
        default: ''
      }
    },
    data () {
      return {
        formValue: this.value,
        formMessage: this.message,
        messageClass: defaultMessageClass,
        formClass: 'pc-input',
        id: 'form_'+this.name
      }
    },
    mounted: function() {
      this.$store.dispatch('define', {key: this.$props.name});
      this.$store.dispatch('set', {key: this.$props.name, value: this.$props.value});
    },
    methods: {
      /**
       * エラーの表示
       * seconds秒後に元に戻す
       * @param {string} message
       */
      showError: function (message: string, seconds: number)
      {
        this.formMessage = message;
        this.messageClass = 'pc-paragraph pc-message pc-paragraphError';
        this.formClass = 'pc-input pc-inputError';

        //timeミリ秒後に値変更
        setTimeout(() =>
        {
          this.formMessage = this.$props.message;
          this.messageClass = defaultMessageClass;
        }, seconds);
      }
    },
    watch: {
      /**
       * 値が変更される度にバックエンドへ通信を行う
       * 成功したら失敗によって変更された値を全てデフォルトに戻す
       * 失敗したらエラー系クラスを付与し、エラーメッセージを表示する
       * バックエンドへ通信中にもう一回この関数が呼ばれたら以前の通信を中止する
       * @param {string} value
       */
      formValue: function (value: string)
      {
        //storeの更新
        this.$store.dispatch('set', {key: this.$props.name, value: value});

        //もし通信中だったらその通信をキャンセルする
        if (source !== null)
        {
          source.cancel();
          source = null;
        }

        //axiosのキャンセルトークンを登録
        const CancelToken = axios.CancelToken;
        source = CancelToken.source();

        //POSTに渡すパラメータ
        let params: URLSearchParams = new URLSearchParams();
        params.append(csrf_key, csrf_value);
        params.append(this.$props.name, value);

        //もしinclude指定があったらその値も同梱する
        if (this.$props.include !== '')
        {
          console.log(this.$props.include);
          console.log(this.$store.state.values[this.$props.include]);
          params.append(this.$props.include, this.$store.state.values[this.$props.include])
        }

        //通信を試みる
        axios.post(site_url+'management/install/validation/'+this.$props.name, params, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          cancelToken: source.token
        })
          .then(() =>
          {
            this.formMessage = this.$props.message;
            this.formClass = 'pc-input';
            this.messageClass = defaultMessageClass;
          })
          .catch((error) =>
          {
            //バリデーションのエラーメッセージを作成
            const data: ValidationData = error.response.data;
            const messages: {[key: string]: string} = data.validation;
            let message: string = '';
            Object.keys(messages).forEach((key) =>
            {
              message += messages[key];
            });

            //文章・クラス変更
            this.showError(message, 3000);
          });
      }
    }
  });
</script>

<style scoped>

</style>