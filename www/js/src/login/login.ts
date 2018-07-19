import Vue from 'vue';
import axios from 'axios';
import {AmdinStyler} from "../AmdinStyler";

/**
 * Vuexのstate
 */
interface loginState {
  id: string,
  password: string
}

//CodeIgniterが提供する変数
declare var csrf_key: string;
declare var csrf_value: string;
declare var site_url: string;

(() =>
{

  //フォームを括るVueインスタンスの作成
  const loginArea = new Vue({
    el: '#pc-loginArea',
    data: {
      show: false,
      showLoader: false,
      id: '',
      password: '',
      error: '&nbsp;'
    },
    mounted: function ()
    {
      //非表示にしてあるボックスの表示
      this.show = true;

      //すでにかかっている制限の取得
      let params: URLSearchParams = new URLSearchParams();
      params.append(csrf_key, csrf_value);
      axios.post(site_url+'management/login/getLimitMessage', params, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'application/x-www-form-urlencoded'
        }
      })
        .then((res) =>
        {
          this.error = res.data.message ? res.data.message : '&nbsp;';
        })
    },
    methods: {
      submit: function ()
      {
        //ローダーの表示
        this.toggleLoader();

        //POSTに渡すパラメータ
        let params: URLSearchParams = new URLSearchParams();
        params.append(csrf_key, csrf_value);
        params.append('id', this.id);
        params.append('password', this.password);

        //通信を試みる
        axios.post(site_url+'management/login/login', params, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        })
          .then(() =>
          {
            //管理画面にリダイレクトする
            window.location.href = site_url+'admin';
          })
          .catch((res) =>
          {
            //ローダーの非表示
            this.toggleLoader(false);

            //エラーの表示
            this.error = res.response.data.message;
          });
      },
      /**
       * いわゆるローダーの表示・非表示を切り替える
       * showをtrue(デフォルト)にすると表示・逆で非表示にする
       * @param {boolean} show
       */
      toggleLoader: function (show: boolean = true)
      {
        this.showLoader = show;
      }
    }
  });

  //スタイル調整クラスのインスタンス化
  const adminStyler = new AmdinStyler();

  //高さを合わせたいクラス名(複数)
  const fullHeightClassNames: Array<string> = ['pc-js-fullHeight'];

  window.onload = () =>
  {
    //height合わせ
    adminStyler.initHeightStyle(fullHeightClassNames);
  }

  //画面リサイズによるheight合わせ
  window.addEventListener('resize', () =>
  {
    adminStyler.initHeightStyle(fullHeightClassNames);
  });
})();
