import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import AdminWindow from './AdminWindow.vue';
import AdminEdit from './AdminEdit.vue';
import SidebarList from './SidebarList.vue';
import {AmdinStyler} from "../AmdinStyler";
import connectModule from './modules/connect';
import editModule from './modules/edit';

//スタイル調整クラスのインスタンス化
const adminStyler = new AmdinStyler();

//高さを合わせたいクラス名(複数)
const fullHeightClassNames: Array<string> = ['ph-js-fullHeight'];
const contentsClassNames: Array<string> = ['ph-js-adminSidebar', 'ph-js-adminArea'];

//Vuexストアの作成
Vue.use(Vuex);
const store = new Vuex.Store({
  modules: {
    connect: connectModule,
    edit: editModule
  }
});

//サイドバー用のルート定義
Vue.use(VueRouter);
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
          component: AdminEdit,
          props: {
            initApi: 'api/admin/call/options/get/',
            name: 'options',
          }
        }
      ]
    }
  ]
});

//全体を括るVueインスタンスの作成
new Vue({
  el: '#ph-admin',
  router,
  store,
  components:{
    'sidebar-list': SidebarList
  }
});

//リサイズ
const resize = () =>
{
  adminStyler.initHeightStyle(fullHeightClassNames);
  adminStyler.initHeightStyle(contentsClassNames);
}
document.addEventListener('DOMContentLoaded', resize, false);
window.addEventListener('resize', resize, false);
