<template>
  <transition name="ph-transition-list">
    <section class="ph-adminWindow" :key="name">
      <h2 class="ph-adminWindowTitle">{{title}}</h2>
      <div class="ph-outerWrapper">
        <section class="ph-listWrapper">
          <table class="ph-index">
            <thead class="ph-indexHead">
            <tr class="ph-indexRow ph-indexHeadRow">
              <template v-for="(field, name) in fields">
                <th class="ph-indexTh ph-reverseColor" v-if="name === idField" v-show="!editFlg">
                  <label class="ph-checkLabel">
                    <input class="ph-checkInput" type="checkbox" :checked="allCheck" @change="checkAll">
                    <span class="ph-checkPseudo"><span class="ph-iconRe ph-reverseColor fas fa-check"></span></span>
                  </label>
                </th>
                <th class="ph-indexTh ph-reverseColor" data-flg="1" v-else-if="name === linkField">
                  <div v-html="field.label"></div>
                </th>
                <th class="ph-indexTh ph-reverseColor" v-else v-show="!editFlg">
                  <div v-html="field.label"></div>
                </th>
              </template>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(datum, index) in data" class="ph-indexRow">
              <template v-for="(value, key) in datum">
                <td class="ph-indexTd" v-if="key === idField" v-show="!editFlg">
                  <label class="ph-checkLabel">
                    <input class="ph-checkInput" type="checkbox" :data-id="index" :value="value" :checked="checks[index]" @change="checkSingle">
                    <span class="ph-checkPseudo"><span class="ph-icon fas fa-check"></span></span>
                  </label>
                </td>
                <td class="ph-indexTd" v-else-if="key === linkField" :class="activeListId === Number(datum[idField]) ? 'ph-indexActive' : ''">
                  <router-link class="ph-linkColor" :to="'/'+name+'/'+datum[idField]" v-html="value"></router-link>
                </td>
                <td class="ph-indexTd" v-else v-show="!editFlg">
                  <div v-html="value"></div>
                </td>
              </template>
            </tr>
            </tbody>
          </table>
        </section>
        <router-view></router-view>
      </div>
    </section>
  </transition>
</template>

<script lang="ts">
  import Vue from 'vue'
  import {AxiosError} from 'axios';

  //initAPIから取得できる'data'のデータ構造
  //キー名がDBのカラム名、値がデータそのもの
  interface dataInterface {
    [key: string]: string|null
  }

  export default Vue.extend({
    props: {
      title: {
        type: String,
        required: true,
        default: ''
      },
      initApi: {
        type: String,
        required: true,
        default: ''
      },
      name: {
        type: String,
        required: true,
        default: ''
      }
    },
    data () {
      return {
        //バックエンドと通信中の時にだけtrueになる
        loading: false,

        //DBでのprimaryフィールド名
        //この文字列と等しいカラムはチェックボックスが表示される
        idField: '',

        //データの名前を表すフィールド名
        //この文字列と等しいカラムは編集ウィンドウを表示するためのリンクが設定される
        linkField: '',

        //DBから取得してきたデータ配列
        //中身はオブジェクトで、{フィールド名: データ}という形になっている
        data: [],

        //DBから取得してきたデータのカラム名配列
        //DBのフィールド名を表すnameとテーブルヘッダーの描写に使われるlabelがある
        fields: [],

        //各行のチェックボックスon/off
        checks: []
      }
    },
    computed: {
      /**
       * 全選択チェックボックスのチェック状態値
       * @return boolean
       */
      allCheck: function(): boolean
      {
        return ! this.checks.some((check: boolean) => ! check);
      },

      /**
       * 現在編集windowを開いているかどうか
       * @return boolean
       */
      editFlg: function(): boolean
      {
        return ! (this.$route.params.id === undefined);
      },

      /**
       * 現在アクティブなリストのIDを返す
       * どのリストも選択されていない場合は0を返す
       * @return number
       */
      activeListId: function(): number
      {
        return this.$route.params.id ? Number(this.$route.params.id) : 0;
      }
    },
    watch: {
      '$route' (to, from)
      {
        //一覧ページを描画した状態で編集ページを呼び出した際は一覧を再取得しない
        if (from.name.replace(/\-edit/, '') === this.name && to.name === this.name+'-edit')
        {
          return;
        }

        this.renderList();
      }
    },
    mounted: function() {
      this.renderList();
    },
    methods: {
      /**
       * 空データを描写する
       * columnNumberが列、countが行の数
       */
      renderDummyList: function(columnNumber: number, count: number)
      {
        //空ヘッダー情報の作成
        const fields: string[] = new Array<string>(columnNumber).fill('<p class="ph-dummyHeaderParagraph">&nbsp;</p>');

        //空データ配列の一行分を作成
        const datum: {[key: string]: string|null} = {};
        Array.prototype.forEach.call(fields, (field: string, index: number) =>
        {
          datum[field+index] = '<p class="ph-dummyCellParagraph">&nbsp;</p>';
        });

        //空データの作成
        const data: {[key: string]: string|null}[] = new Array<{[key: string]: string|null}>(count).fill(datum);

        this.fields = fields;
        this.data = data;
      },
      /**
       * データを取得し、描写する
       */
      renderList: function()
      {
        //loading中アイコンとダミーリストを表示する
        this.loading = true;

        //ダミーテーブルの描写
        //0.3秒後待ってその間にサーバーサイドから応答があったらキャンセルする
        const timer = setTimeout(() =>
        {
          this.renderDummyList(10, 10);
        }, 300);

        //データの入手
        this.$store.dispatch('connectAPI', {api: this.$props.initApi, data: {}})
          .then(() =>
          {
            //ダミーテーブル描写のキャンセル
            clearTimeout(timer);

            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;

            //データをVuexから取得
            const data: {
              fields: {[key: string]: string},
              id: string,
              link: string,
              data: dataInterface[]
            } = this.$store.getters.getData(['fields', 'id', 'link', 'data']);

            //データの数だけchecksにfalseを設定する
            this.checks = new Array(data.data.length).fill(false);

            //登録
            this.fields = data.fields;
            this.idField = data.id;
            this.linkField = data.link;
            this.data = data.data;
          })
          .catch((data: AxiosError) =>
          {
            //ダミーテーブル描写のキャンセル
            clearTimeout(timer);

            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;
          });
      },
      checkAll: function (event: Event)
      {
        //全選択チェックボックス
        const target: HTMLInputElement = <HTMLInputElement>event.target;

        //各行のチェックボックスを全選択チェックボックスと同期させる
        this.checks = new Array(this.data.length).fill(target.checked);
      },
      checkSingle: function (event: Event)
      {
        //値が切り替わったチェックボックス
        const target: HTMLInputElement = <HTMLInputElement>event.target;

        //値を同期させる
        this.$set(this.checks, target.getAttribute('data-id'), target.checked);
      }
    }
  });
</script>

<style scoped type="text/scss">
  .ph-transition-list-enter-active, .ph-transition-list-leave-active {
    transition: opacity .1s ease-in-out, top .1s ease;
  }
  .ph-transition-list-enter, .ph-transition-list-leave-to{
    opacity: 0;
    top: 30px;
  }
</style>