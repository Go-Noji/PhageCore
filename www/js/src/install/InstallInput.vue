<template>
  <div>
    <div>
      <h3 class="ph-subTitle">
        {{title}}
        <transition name="icon-fade">
          <span v-if="success"><i class="ph-icon fas fa-check-circle"></i></span>
        </transition>
      </h3>
      <p class="ph-paragraph">{{description}}</p>
    </div>
    <div>
      <input :name="name" :value="formValue" :placeholder="placeholder" :class="formClass" v-model="formValue" :type="formType" @blur="showValidationResult">
      <button type="button" class="ph-button" v-if="type === 'password'" @click="toggleShowPassword">
        <span v-show="showPassword"><i class="fas fa-eye-slash"></i></span>
        <span v-show="!showPassword"><i class="fas fa-eye"></i></span>
      </button>
      <p v-html="formMessage" class="ph-paragraph ph-message ph-paragraphError"></p>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue'
  import axios, {CancelTokenSource, AxiosPromise, AxiosError} from 'axios';

  //CodeIgniterが提供する変数
  declare var csrf_key: string;
  declare var csrf_value: string;
  declare var site_url: string;

  //デフォルトのフォームクラス
  const defaultFormClass: string = 'ph-input';

  //デフォルトのエラーメッセージ
  const defaultMessage: string = '&nbsp;';

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
      description: {
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
        success: false,
        preMessage: defaultMessage,
        formValue: this.value,
        formMessage: defaultMessage,
        formClass: defaultFormClass,
        formType: this.$props.type,
        showPassword: false
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
        this.validate(value)
          .then(() =>
          {
            //preMessageの初期化
            this.setPreMessage();
          })
          .catch((error: AxiosError) =>
          {
            //エラーメッセージをpreMessageに格納
            this.setPreMessage(this.createErrorMessage(error.response.data.validation));
          });
      }
    },
    mounted: function() {
      //Vuexへ送信用のデータ領域を確保する
      this.$store.dispatch('define', {key: this.$props.name});
      this.$store.dispatch('set', {key: this.$props.name, value: this.$props.value});
    },
    methods: {
      /**
       * バックエンドへ値を渡し、検証を試みる
       * @param {string} message
       * @return {axios}
       */
      validate: function (value: string): AxiosPromise
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
          params.append(this.$props.include, this.$store.state.values[this.$props.include])
        }

        //通信を試みる
        return axios.post(site_url+'management/install/validation/'+this.$props.name, params, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          cancelToken: source.token
        });
      },
      /**
       * エラーメッセージの予約
       * showValidationResultによって呼ばれるエラーメッセージをpreMessageに格納する
       * messageを省略(null)にするとdefaultMessageが格納される
       * @param {string} message
       */
      setPreMessage: function (message: string|null = null)
      {
        this.preMessage = message === null ? defaultMessage : message;
      },
      /**
       * エラーメッセージを作成する
       * validationオブジェクト内の値を文字列として結合して返す
       * @param {{[key: string]: string}} validation
       * @return string
       */
      createErrorMessage: function (validation: {[key: string]: string}): string
      {
        //バリデーションのエラーメッセージを作成
        let message: string = '';
        Object.keys(validation).forEach((key) =>
        {
          message += validation[key];
        });
        return message;
      },
      /**
       * 実際に現在の情報を表示する
       * successはデフォルトでtrue、
       * preMessageはnullを指定するとdefaultMessage,
       * formClassはnullを指定するとdefaultFormClassを参照する
       */
      renderMessage: function(success: boolean = true, preMessage: string|null = null, formClass: string|null = null)
      {
        this.success = success;
        this.formMessage = preMessage === null ? defaultMessage : preMessage;
        this.formClass = formClass === null ? defaultFormClass : formClass;
      },
      /**
       * エラーの表示 or 成功表示
       * エラーとしてmessageを表示するが、messageが空だったら再度検証を挟む
       * @param {string} message
       */
      showValidationResult: function ()
      {
        //エラーメッセージが空だったら再バリデーション
        // それでも問題が無ければ表示を成功時のものにする
        if (this.preMessage === defaultMessage)
        {
          this.validate(this.formValue)
            .then(() =>
            {
              //表示の変更
              this.renderMessage();
            })
            .catch((error: AxiosError) =>
            {
              //エラーメッセージをpreMessageに格納
              this.setPreMessage(this.createErrorMessage(error.response.data.validation));

              //表示の変更
              this.renderMessage(false, this.preMessage, 'ph-input ph-inputError');

              //this.preMessageの初期化
              this.setPreMessage();
            });
          return;
        }

        //表示の変更
        this.renderMessage(false, this.preMessage, 'ph-input ph-inputError');
      },
      /**
       * パスワードを表示するためにtype属性をpassword, textでトグルする
       */
      toggleShowPassword: function ()
      {
        this.formType = this.formType === 'password' ? 'type' : 'password';
        this.showPassword = ! this.showPassword;
      }
    }
  });
</script>

<style scoped>
  .icon-fade-enter-active, .icon-fade-leave-active{
    transition: all .5s ease;
  }
  .icon-fade-enter, .icon-fade-leave-to{
    opacity: 0;
    margin-left: -5px;
  }
</style>