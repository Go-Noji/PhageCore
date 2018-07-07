import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios';

//CodeIgniterが提供する変数
declare var csrf_key: string;
declare var csrf_value: string;
declare var site_url: string;

(() =>
{
  //フォームを括るVueインスタンスの作成
  const loginForm = new Vue({
    el: '#loginForm'
  });
})();
