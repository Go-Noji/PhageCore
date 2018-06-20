<template>
  <div>
    <div>
      <h3 class="pc-sub_title">{{title}}</h3>
      <p :class="message_class">{{form_message}}</p>
    </div>
    <div>
      <input :id="id" :name="name" :value="form_value" :placeholder="placeholder" :class="form_class" v-model="form_value" :type="type">
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue'
  import axios from 'axios';

  //CodeIgniterが提供する変数
  declare var csrf_key: string;
  declare var csrf_value: string;
  declare var site_url: string;

  //バリデーションを行ったときに返ってくるデータ
  //validationにバリデーションのエラーメッセージが入る
  interface Validation_data{
    validation: string
  }

  //デフォルトのメッセージクラス
  const default_message_class: string = 'pc-paragraph pc-message';

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
        form_value: '',
        form_message: '',
        message_class: default_message_class,
        form_class: 'pc-input',
        id: ''
      }
    },
    mounted: function() {
      this.form_message = this.$props.message;
      this.form_value = this.$props.value;
      this.id = 'form_'+this.$props.name;
    },
    watch: {
      form_value: function (value: string)
      {
        //POSTに渡すパラメータ
        let params: URLSearchParams = new URLSearchParams();
        params.append(csrf_key, csrf_value);
        params.append(this.$props.name, value);

        //もしinclude指定があったらその値も同梱する
        if (this.$props.include !== '')
        {
          const target: HTMLInputElement = <HTMLInputElement>document.getElementById('form_'+this.$props.include);
          params.append(this.$props.include, target.value)
        }

        axios.post(site_url+'management/install/validation/'+this.$props.name, params, {headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          }})
          .then((response) =>
          {
            this.form_message = this.$props.message;
            this.form_class = 'pc-input';
            this.message_class = default_message_class;
          })
          .catch((error) =>
          {
            const data: Validation_data = error.response.data;

            //文章・クラス変更
            this.form_message = data.validation;
            this.message_class = 'pc-paragraph pc-message pc-paragraph_error';
            this.form_class = 'pc-input pc-input_error';

            //timeミリ秒後に値変更
            setTimeout(() =>
            {
              this.form_message = this.$props.message;
              this.message_class = default_message_class;
            }, 3000);
          });
      }
    }
  });
</script>

<style scoped>

</style>