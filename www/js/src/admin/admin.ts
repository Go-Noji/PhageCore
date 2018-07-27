import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import axios from 'axios';
import AdminWindow from './AdminWindow.vue';
import SidebarList from './SidebarList.vue';
import {AmdinStyler} from "../AmdinStyler";

//CodeIgniterが提供する変数
declare var csrf_key: string;
declare var csrf_value: string;
declare var site_url: string;

{
  //スタイル調整クラスのインスタンス化
  const adminStyler = new AmdinStyler();

  //高さを合わせたいクラス名(複数)
  const fullHeightClassNames: Array<string> = ['ph-js-fullHeight'];
  const contentsClassNames: Array<string> = ['ph-js-adminSidebar', 'ph-js-adminArea'];

  //VueRouterの使用を宣言
  Vue.use(VueRouter);

  //サイドバー用のルート定義
  const router: VueRouter = new VueRouter({
    routes: [
      {
        path: '/content',
        component: AdminWindow,
        props: {
          api: 'api/select/call/content/multiple'
        }
      }
    ]
  });

  //全体を括るVueインスタンスの作成
  const admin = new Vue({
    el: '#ph-admin',
    router,
    components:{
      'admin-window': AdminWindow,
      'sidebar-list': SidebarList
    },
    methods: {}
  });

  window.onload = () =>
  {
    //height合わせ
    adminStyler.initHeightStyle(fullHeightClassNames);
    adminStyler.initHeightStyle(contentsClassNames, - document.querySelector('.ph-js-adminHeader').getBoundingClientRect().height);
  }

  //画面リサイズによるheight合わせ
  window.addEventListener('resize', () =>
  {
    adminStyler.initHeightStyle(fullHeightClassNames);
    adminStyler.initHeightStyle(contentsClassNames, - document.querySelector('.ph-js-adminHeader').getBoundingClientRect().height);
  }, false);
}
