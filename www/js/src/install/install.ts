import Vue from 'vue';
import Vuex from 'vuex';
import {Installer} from "./installer";
import InstallInput from './InstallInput.vue';
import axios from 'axios';

/**
 * Vuexのstate
 */
interface installStoreInterface{
  values: {[key: string]: string|null}
}

/**
 * Vuexのdefinitionミューテーションに対するペイロード
 */
interface definitionPayload {
  key: string
}

/**
 * VuexのsetValueミューテーションに対するペイロード
 */
interface setValuePayload {
  key: string,
  value: string
}

//バリデーションを行ったときに返ってくるデータ
//validationにバリデーションのエラーメッセージが入る
interface ValidationData{
  validation: {[key: string]: string}
}

/**
 * eventターゲットのラップ用
 */
interface HTMLElementEvent<T extends HTMLElement> extends Event{
  target: T
}

//CodeIgniterが提供する変数
declare var csrf_key: string;
declare var csrf_value: string;
declare var site_url: string;

(() =>
{
  //インストール用クラス
  const installer = new Installer();

  //高さを合わせたいクラス名(複数)
  const fullHeightClassNames: Array<string> = ['pc-js-full_height'];

  //Vuexストアの作成
  Vue.use(Vuex);
  const store = new Vuex.Store({
    state: {
      values: {}
    },
    getters: {
      /**
       * keyに入力された値を返す
       * 未定義でも空文字を返すが、consoleにメッセージを出す
       * @param {installStoreInterface} state
       * @return {(key: string) => (string | string | null)}
       */
      getValue: (state: installStoreInterface) => (key: string) =>
      {
        //対象のkeyにまだ値がセットされていなかったら空文字を返す
        if (state.values[key] === null)
        {
          console.log('installStore: values.'+key+' not have value.');
          return '';
        }

        return state.values[key];
      }
    },
    mutations: {
      /**
       * フォームのnameを登録する
       * @param {{values: definitionValues}} state
       * @param {definitionPayload} payload
       */
      definitionInput(state: installStoreInterface, payload: definitionPayload)
      {
        state.values[payload.key] = null;
      },

      /**
       * フォームのvalueを登録する
       * 先にdefinitionInputを使って要素を定義しておく必要がある
       * @param {installStoreInterface} state
       * @param {setValuePayload} payload
       */
      setValue(state: installStoreInterface, payload: setValuePayload)
      {
        //対象のkeyが見つからなかったら処理を終了させる
        if (state.values[payload.key] === undefined)
        {
          console.log('installStore: values.'+payload.key+' is undefined.');
          return;
        }

        state.values[payload.key] = payload.value;
      }
    },
    actions: {
      define({commit}, payload: definitionPayload)
      {
        commit('definitionInput', payload);
      },
      set({commit}, payload: definitionPayload)
      {
        commit('setValue', payload);
      }
    }
  });

  //フォームを括るVueインスタンスの作成
  const installForm = new Vue({
    el: '#installForm',
    store,
    data: {
      dbError: '　',
      showLoader: false
    },
    components:{
      'install-input': InstallInput
    },
    methods: {
      /**
       * インストールを試みる
       * @param {HTMLElementEvent<HTMLInputElement>} event
       */
      install: function (event: HTMLElementEvent<HTMLInputElement>)
      {
        //ローダーを表示する
        this.toggleLoader();

        //全ての入力情報を入手
        const values = this.$store.state.values;

        //POSTに渡すパラメータ
        let params: URLSearchParams = new URLSearchParams();
        params.append(csrf_key, csrf_value);
        Object.keys(values).forEach((key: string) =>
        {
          params.append(key, values[key]);
        });

        //通信を試みる
        axios.post(site_url+'management/install/validation/all', params, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        })
          .then((response) =>
          {
            //ローダーを非表示する
            this.toggleLoader(false);

            //ログイン画面にリダイレクトする
            window.location.href = site_url+'management/login';
          })
          .catch((error) =>
          {
            //ローダーを非表示する
            this.toggleLoader(false);

            //エラーメッセージから通常メッセージに戻すまでの秒数
            const returnSeconds: number = 3000;

            //バリデーションエラーを各コンポーネントに表示する
            const data: ValidationData = error.response.data;
            const messages: {[key: string]: string} = data.validation;
            Object.keys(messages).forEach((key) =>
            {
              //DBエラー文の表示
              if (key === 'db_error')
              {
                this.dbError = messages[key];
                setTimeout(() =>
                {
                  this.dbError = '　';
                }, returnSeconds);
              }

              //各コンポーネントの表示
              else if(this.$refs[key] !== undefined)
              {
                this.$refs[key].renderMessage(false, messages[key] ? messages[key] : null, messages[key] ? 'pc-input pc-inputError' : 'pc-input');
              }
            });
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

  window.onload = () =>
  {
    //height合わせ
    installer.initHeightStyle(fullHeightClassNames);
  }

  //画面リサイズによるheight合わせ
  window.addEventListener('resize', () =>
  {
    installer.initHeightStyle(fullHeightClassNames);
  });
})();
