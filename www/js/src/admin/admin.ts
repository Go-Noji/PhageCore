import Vue from 'vue';
import Vuex, {Module} from 'vuex';
import VueRouter from 'vue-router';
import Axios, {
  AxiosError,
  AxiosRequestConfig,
  AxiosResponse, CancelTokenSource, CancelTokenStatic
} from 'axios';
import AdminWindow from './AdminWindow.vue';
import AdminEdit from './AdminEdit.vue';
import SidebarList from './SidebarList.vue';
import {AmdinStyler} from "../AmdinStyler";

/**
 * Vuexのstate
 */
interface AdminState{
  lastApi: string,
  data: {[key: string]: string},
  error: AxiosError,
  source: CancelTokenSource|null
}

/**
 * EditModuleのためのInterface
 */
interface EditData {
  api: string,
  value: string,
  connect: boolean,
  success: boolean
}
interface EditState {
  id: number,
  data: {
    [key: string]: EditData
  }
}

{
  //スタイル調整クラスのインスタンス化
  const adminStyler = new AmdinStyler();

  //高さを合わせたいクラス名(複数)
  const fullHeightClassNames: Array<string> = ['ph-js-fullHeight'];
  const contentsClassNames: Array<string> = ['ph-js-adminSidebar', 'ph-js-adminArea'];

  //バックエンドとの通信専用Vuexモジュール
  const connectModule: Module<AdminState, AdminState> = {
    namespaced: true,
    state: {
      lastApi: '',
      data: {},
      error: new class implements AxiosError {
        code: string;
        config: AxiosRequestConfig;
        message: string;
        name: string;
        request: any;
        response: AxiosResponse;
        stack: string;
      },
      source: null
    },
    mutations: {
      /**
       * データを初期化する
       * @param state
       */
      init(state: AdminState) {
        state.data = {};
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
      cancel(state: AdminState, data: { token: CancelTokenStatic, api: string }) {
        state.source = data.token.source();
        state.lastApi = data.api;
      },
      /**
       * 成功データを登録する
       * @param state
       * @param data
       */
      success(state: AdminState, data: { [key: string]: string }) {
        state.data = data;
        state.source = null;
      },
      /**
       * 失敗データを登録する
       * @param state
       * @param data
       */
      failure(state: AdminState, data: AxiosError) {
        state.error = data;
        state.data = data.response.data;
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
      connect({commit, state}, payload: { api: string, data: { [key: string]: string | { [key: string]: string } } }) {
        //もし同じAPIが通信中だったらその通信をキャンセルする
        if (state.source !== null && state.lastApi === payload.api) {
          state.source.cancel();
        }

        //Axiosのキャンセルトークンを登録
        commit('cancel', {token: Axios.CancelToken, api: payload.api});

        //データの初期化を行う
        commit('init');

        //POSTに渡すパラメータ
        let params: URLSearchParams = new URLSearchParams();
        params.append(csrf_key, csrf_value);

        //追加パラメータ
        Object.keys(payload.data).forEach((key: string) => {
          const value: string | { [key: string]: string } = payload.data[key];
          if (typeof value === 'string') {
            params.append(key, value);
          }
          else if (typeof value === 'object') {
            Object.keys(value).forEach((name: string) => {
              params.append(key + '[' + name + ']', value[name]);
            });
          }
        });

        //通信を試みる
        return Axios.post(site_url + payload.api, params, {
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
      connectAPI({dispatch, commit}, payload: { api: string, data: { [key: string]: string } }) {
        return new Promise((resolve, reject) => {
          dispatch('connect', payload)
            .then((response: { data: { [key: string]: string } }) => {
              commit('success', response.data);
              resolve();
            })
            .catch((error: AxiosError) => {
              commit('failure', error);
              reject();
            });
        });
      }
    },
    getters: {
      /**
       * 現在通信ならtrue, そうでなければfalseが返る
       * @param state
       */
      isConnect: (state: AdminState) => {
        return state.source === null ? false : true;
      }
    }
  };

  //編集画面ごとの更新用モジュール
  const editModule: Module<EditState, AdminState> = {
    namespaced: true,
    state: {
      id: 0,
      data: {}
    },
    mutations: {
      /**
       * 編集する対象のIDと各項目データをセットする
       * @param state
       * @param payload
       */
      init(state: EditState, payload: {id: number, data: {[key: string]: EditData}})
      {
        state.id = payload.id;
        state.data = payload.data;
      },

      /**
       * 項目の値を変更する
       * @param state
       * @param payload
       */
      change(state: EditState, payload: {key: string, value: string})
      {
        state.data[payload.key].value = payload.value;
      },

      /**
       * 特定の項目を更新キューに入れる
       * payloadのkeyが空文字だった場合は全ての項目がキューに入る
       * @param state
       * @param payload
       */
      queue(state: EditState, payload: {key: string})
      {
        //特定の項目を指定されている場合
        if (payload.key !== '')
        {
          state.data[payload.key].connect = true;
          return;
        }

        //全ての項目をキューに入れる
        for(let k of Object.keys(state.data))
        {
          state.data[k].connect = true;
        }
      },

      /**
       * 更新成功時に特定の項目のsuccessをtrueに、errorを空文字にする
       * @param state
       * @param payload
       */
      then(state: EditState, payload: {key: string})
      {
        state.data[payload.key].success = true;
        state.data[payload.key].connect = false;
      },

      /**
       * 更新失敗時に特定項目のsuccessをfalseにし、errorを登録する
       * @param state
       * @param payload
       */
      catch(state: EditState, payload: {key: string, error: string})
      {
        state.data[payload.key].success = false;
        state.data[payload.key].connect = false;
      }
    },
    actions: {
      /**
       * 現在キューに入っている項目を全てバックエンドと通信して更新する
       * 通信は親ModuleであるAdminModuleのconnectをdispatchして行われる
       * 成功・失敗に関係なく通信済みの項目はキューから除外される
       * @param commit
       * @param state
       */
      submit({commit, state, getters})
      {
        //非同期通信のためのタスク配列を用意
        const task = [];

        //項目ごとに処理
        for (let k of Object.keys(state.data))
        {
          //対象が更新対象でない場合は飛ばす
          if ( ! state.data[k].connect)
          {
            continue;
          }

          //非同期タスクの追加
          //親Moduleのconnectをdispatchする
          const data: {data: {[key: string]: string}, segments: Array<number>} = {data: {}, segments: [state.id]};
          data.data[k] = state.data[k].value;
          task.push(new Promise((resolve, reject) => {
            this.dispatch('connect/connectAPI', {api: state.data[k].api, data: data}, {root: true})

            //更新成功
              .then(() =>
              {
                commit('then', {key: k});
                resolve();
              })

              //更新失敗
              .catch(() =>
              {
                commit('catch', {key: k});
                reject();
              });
          }));
        }

        //全ての非同期タスクを実行する
        return Promise.all(task);
      }
    }
  };

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
  const admin = new Vue({
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
}
