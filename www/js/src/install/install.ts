import Vue from 'vue';
import Component from 'vue-class-component';
import axios from 'axios';
import {Installer} from "./installer";

//CodeIgniterが提供する変数
declare var csrf_key: string;
declare var csrf_value: string;
declare var site_url: string;

//バリデーションを行ったときに返ってくるデータ
//validationにバリデーションのエラーメッセージが入る
interface Validation_data{
  validation: string
}

(() =>
{
  //インストール用クラス
  const installer = new Installer();

  //高さを合わせたいクラス名(複数)
  const fullHeightClassNames: Array<string> = ['pc-js-full_height'];

  //各Vueインスタンスの作成
  const admin = new Vue({
    el: '#admin',
    data: {
      value: '',
      message: 'Phage Core上で表示される管理者名です。',
      message_original: 'Phage Core上で表示される管理者名です。',
      message_class: 'pc-paragraph',
      form_class: 'pc-input'
    },
    watch: {
      value: function (value: string, old_value: string)
      {
        //POSTに渡すパラメータ
        let params: URLSearchParams = new URLSearchParams();
        params.append(csrf_key, csrf_value);
        params.append('admin', value);

        axios.post(site_url+'management/install/validation/admin', params, {headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          }})
          .then((response) =>
          {
            console.log(response);
            this.form_class = 'pc-input';
          })
          .catch((error) =>
          {
            const data: Validation_data = error.response.data;

            //文章・クラス変更
            this.message = data.validation;
            this.message_class = 'pc-paragraph pc-paragraph_error';
            this.form_class = 'pc-input pc-input_error';

            //timeミリ秒後に値変更
            setTimeout(() =>
            {
              this.message = this.message_original;
              this.message_class = 'pc-paragraph';
            }, 3000);
          });
      }
    }
  });

  window.onload = () =>
  {
    //height合わせ
    installer.initHeightStyle(fullHeightClassNames);
  }
})();
