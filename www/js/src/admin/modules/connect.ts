import {Module} from 'vuex';
import Axios, {AxiosError, AxiosRequestConfig, AxiosResponse, CancelTokenStatic} from 'axios';
import {AdminState} from '../interface';

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
    init(state: AdminState)
    {
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
    cancel(state: AdminState, data: {token: CancelTokenStatic, api: string})
    {
      state.source = data.token.source();
      state.lastApi = data.api;
    },
    /**
     * 成功データを登録する
     * @param state
     * @param data
     */
    success(state: AdminState, data: {[key: string]: string})
    {
      state.data = data;
      state.source = null;
    },
    /**
     * 失敗データを登録する
     * @param state
     * @param data
     */
    failure(state: AdminState, data: AxiosError)
    {
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
    connect({commit, state}, payload: {api: string, data: {[key: string]: string | {[key: string]: string}}})
    {
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
      Object.keys(payload.data).forEach((key: string) =>
      {
        const value: string | {[key: string]: string} = payload.data[key];
        if (typeof value === 'string') {
          params.append(key, value);
        }
        else if (typeof value === 'object') {
          Object.keys(value).forEach((name: string) =>
          {
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
    isConnect: (state: AdminState) =>
    {
      return state.source === null ? false : true;
    }
  }
};

export default connectModule;