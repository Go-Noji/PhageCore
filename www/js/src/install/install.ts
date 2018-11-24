import Vue from 'vue';
import Vuex from 'vuex';
import {AmdinStyler} from "../AmdinStyler";
import InstallInput from './InstallInput.vue';
import axios from 'axios';
import {InstallStoreInterface, DefinitionPayload, HTMLElementEvent, SetValuePayload, ValidationData} from './interface';

//インストール用クラス
const adminStyler = new AmdinStyler();

//高さを合わせたいクラス名(複数)
const fullHeightClassNames: Array<string> = ['ph-js-full_height'];

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
     * @param {InstallStoreInterface} state
     * @return {(key: string) => (string | string | null)}
     */
    getValue: (state: InstallStoreInterface) => (key: string) =>
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
     * @param {DefinitionPayload} payload
     */
    definitionInput(state: InstallStoreInterface, payload: DefinitionPayload)
    {
      state.values[payload.key] = null;
    },

    /**
     * フォームのvalueを登録する
     * 先にdefinitionInputを使って要素を定義しておく必要がある
     * @param {InstallStoreInterface} state
     * @param {SetValuePayload} payload
     */
    setValue(state: InstallStoreInterface, payload: SetValuePayload)
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
    define({commit}, payload: DefinitionPayload)
    {
      commit('definitionInput', payload);
    },
    set({commit}, payload: DefinitionPayload)
    {
      commit('setValue', payload);
    }
  }
});

//フォームを括るVueインスタンスの作成
new Vue({
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
          //ログイン画面にリダイレクトする
          window.location.href = site_url+'management/login?first=1';
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
              this.$refs[key].renderMessage(false, messages[key] ? messages[key] : null, messages[key] ? 'ph-input ph-inputError' : 'ph-input');
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
  adminStyler.initHeightStyle(fullHeightClassNames);
}

//画面リサイズによるheight合わせ
window.addEventListener('resize', () =>
{
  adminStyler.initHeightStyle(fullHeightClassNames);
});
