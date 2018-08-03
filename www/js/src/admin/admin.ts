import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import axios from 'axios';
import AdminWindow from './AdminWindow.vue';
import SidebarList from './SidebarList.vue';
import {AmdinStyler} from "../AmdinStyler";
import {Checkbox} from "../Checkbox";

//CodeIgniterが提供する変数
declare var csrf_key: string;
declare var csrf_value: string;
declare var site_url: string;

{
  //スタイル調整クラスのインスタンス化
  const adminStyler = new AmdinStyler();

  //チェックボックス制御クラスのインスタンス化
  const checkbox = new Checkbox();

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
        name: 'content',
        component: AdminWindow,
        props: {
          initApi: 'api/admin/call/content/multiple',
          title: 'コンテンツ',
          name: 'content'
        }
      },
      {
        path: '/options',
        name: 'options',
        component: AdminWindow,
        props: {
          initApi: 'api/admin/call/options/multiple',
          title: '設定',
          name: 'options'
        },
        children: [
          {
            path: ':id',
            name: 'options-edit',
            component: {template: '<section class="ph-innerWrapper">{{$route.params.id}}</section>'}
          }
        ]
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

    //チェックボックスの登録
    checkbox.setTargetClassName('ph-js-checkId');
    checkbox.registerAllCheck('ph-js-checkAll');
  }

  //画面リサイズによるheight合わせ
  window.addEventListener('resize', () =>
  {
    adminStyler.initHeightStyle(fullHeightClassNames);
    adminStyler.initHeightStyle(contentsClassNames, - document.querySelector('.ph-js-adminHeader').getBoundingClientRect().height);
  }, false);
}
