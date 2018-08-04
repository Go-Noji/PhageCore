import Vue from 'vue';
import Vuex from 'vuex';
import VueRouter from 'vue-router';
import axios, {
  CancelToken,
  AxiosPromise,
  AxiosError,
  AxiosRequestConfig,
  AxiosResponse, CancelTokenSource, CancelTokenStatic
} from 'axios';
import AdminWindow from './AdminWindow.vue';
import AdminEdit from './AdminEdit.vue';
import SidebarList from './SidebarList.vue';
import {AmdinStyler} from "../AmdinStyler";
import {Checkbox} from "../Checkbox";

/**
 * Vuexのstate
 */
interface adminState{
  success: {[key: string]: string},
  error: AxiosError,
  source: CancelTokenSource|null
}

{
  //スタイル調整クラスのインスタンス化
  const adminStyler = new AmdinStyler();

  //チェックボックス制御クラスのインスタンス化
  const checkbox = new Checkbox();

  //高さを合わせたいクラス名(複数)
  const fullHeightClassNames: Array<string> = ['ph-js-fullHeight'];
  const contentsClassNames: Array<string> = ['ph-js-adminSidebar', 'ph-js-adminArea'];

  //Vuexストアの作成
  Vue.use(Vuex);
  const store = new Vuex.Store({
    state: {
      success: {},
      error: {},
      source: null
    },
    getters: {
      /**
       * バックエンドと通信した後の成功データをキーを指定して取得する
       * キーに該当するデータが存在しない場合はundefinedが設定される
       * @param state
       */
      getData: (state: adminState) => (keys: string[]) =>
      {
        const data: {[key: string]: string} = {};
        Array.prototype.forEach.call(keys, (key: string) =>
        {
          if (state.success[key] === undefined)
          {
            console.log('[Phage Core]: store.getters.getDataの引数に指定されたキー「'+key+'」が見つかりません。値はundefinedがセットされました。');
          }

          data[key] = state.success[key];
        });
        return data;
      },
      /**
       * 現在通信ならtrue, そうでなければfalseが返る
       * @param state
       */
      isConnect: (state: adminState) =>
      {
        return state.source === null ? false : true;
      }
    },
    mutations: {
      /**
       * データを初期化する
       * @param state
       */
      init (state: adminState)
      {
        state.success = {};
        state.error = new class implements AxiosError {
          code: string;
          config: AxiosRequestConfig;
          message: string;
          name: string;
          request: any;
          response: AxiosResponse;
          stack: string;
        };
      },
      /**
       * Axiosのキャンセルトークンを登録する
       * @param state
       * @param token
       */
      cancel (state: adminState, token: CancelTokenStatic)
      {
        state.source = token.source();
      },
      /**
       * 成功データを登録する
       * @param state
       * @param data
       */
      success (state: adminState, data: {[key: string]: string})
      {
        state.success = data;
        state.source = null;
      },
      /**
       * 失敗データを登録する
       * @param state
       * @param data
       */
      failure (state: adminState, data: AxiosError)
      {
        state.error = data;
        state.source = null;
      }
    },
    actions: {
      /**
       * バックエンドとの通信を行い、Promiseを返す
       * connectAPI内で使う
       * @param commit
       * @param payload
       */
      connect({commit, state}, payload: {api: string, data: {[key: string]: string}})
      {
        //もし通信中だったらその通信をキャンセルする
        if (state.source !== null)
        {
          state.source.cancel();
        }

        //axiosのキャンセルトークンを登録
        commit('cancel', axios.CancelToken);

        //データの初期化を行う
        commit('init');

        //POSTに渡すパラメータ
        let params: URLSearchParams = new URLSearchParams();
        params.append(csrf_key, csrf_value);

        //追加パラメータ
        Object.keys(payload.data).forEach((key: string) =>
        {
          params.append(key, payload.data[key]);
        });

        //通信を試みる
        return axios.post(site_url+payload.api, params, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          cancelToken: state.source.token
        });
      },
      /**
       * バックエンドとの通信を行い、Promiseを返す
       * 返るPromiseにデータは無く、データの取得はVuexに任せる形になる
       * .thenで成功、.catchで失敗の点は変わらず
       * @param dispatch
       * @param commit
       * @param payload
       */
      connectAPI({dispatch, commit}, payload: {api: string, data: {[key: string]: string}})
      {
        return new Promise((resolve, reject) =>
        {
          dispatch('connect', payload)
            .then((response: {data: {[key: string]: string}}) =>
            {
              commit('success', response.data);
              resolve();
            })
            .catch((error: AxiosError) =>
            {
              console.log(error);
              commit('failure', error);
              reject();
            });
        });
      }
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
            }
          }
        ]
      }
    ]
  });

  //全体を括るVueインスタンスの作成
  const admin = new Vue({
    el: '#ph-admin',
    router,
    store,
    components:{
      'sidebar-list': SidebarList
    }
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
