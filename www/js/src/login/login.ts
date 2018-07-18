import Vue from 'vue';
import Vuex from 'vuex';
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
      id: '',
      password: ''
    },
    mounted: function ()
    {
      this.show = true;
    },
    methods: {
      submit: function ()
      {
        console.log(this.id)
        console.log(this.password)
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
