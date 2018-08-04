<template>
  <transition name="list">
    <section class="ph-adminWindow" :key="name">
      <h2 class="ph-adminWindowTitle">{{title}}</h2>
      <div class="ph-outerWrapper">
        <section class="ph-innerWrapper">
          <h3>{{title}}一覧</h3>
          <table class="ph-index">
            <thead class="ph-indexHead">
            <tr class="ph-indexRow ph-indexHeadRow">
              <th class="ph-indexTh ph-reverseColor" v-for="field in fields">
                <label class="ph-checkLabel" v-if="field === idField">
                  <input class="ph-checkInput ph-js-checkAll" type="checkbox">
                  <span class="ph-checkPseudo"><span class="ph-iconRe ph-reverseColor fas fa-check"></span></span>
                </label>
                <div v-else v-html="field"></div>
              </th>
            </tr>
            </thead>
            <tbody>
            <tr class="ph-indexRow" v-for="datum in data">
              <td class="ph-indexTd" v-for="(value, key) in datum">
                <label class="ph-checkLabel" v-if="key === idField">
                  <input class="ph-checkInput ph-js-checkId" type="checkbox" :value="value">
                  <span class="ph-checkPseudo"><span class="ph-icon fas fa-check"></span></span>
                </label>
                <router-link class="ph-linkColor" v-else-if="key === linkField" :to="'/'+name+'/'+datum[idField]" v-html="value"></router-link>
                <div v-else v-html="value"></div>
              </td>
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
  import axios, {AxiosPromise, AxiosError} from 'axios';

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
        loading: false,
        idField: '',
        linkField: '',
        data: {},
        fields: {}
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
        this.renderDummyList(10, 10);

        //データの入手
        this.$store.dispatch('connectAPI', {api: this.$props.initApi, data: {}})
          .then(() =>
          {
            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;

            //データをVuexから取得
            const data: {fields: Array<string>, id: string, link: string, data: Array<{[key: string]: string|null}>} = this.$store.getters.getData(['fields', 'id', 'link', 'data']);

            //登録
            this.fields = data.fields;
            this.idField = data.id;
            this.linkField = data.link;
            this.data = data.data;
          })
          .catch((data: AxiosError) =>
          {
            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;

            console.log(data);
          });
      }
    }
  });
</script>

<style scoped type="text/scss">
  .list-enter-active, .list-leave-active {
    transition: opacity .1s ease-in-out, top .1s ease;
  }
  .list-enter, .list-leave-to{
    opacity: 0;
    top: 30px;
  }
</style>