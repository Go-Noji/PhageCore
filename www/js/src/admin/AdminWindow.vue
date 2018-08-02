<template>
  <transition name="list">
    <section class="ph-adminWindow" :key="key">
      <h2 class="ph-adminWindowTitle">{{title}}</h2>
      <section class="ph-innerWrapper">
        <h3>{{title}}一覧</h3>
        <table class="ph-index">
          <thead class="ph-indexHead">
          <tr class="ph-indexRow ph-indexHeadRow">
            <th class="ph-indexTh ph-reverseColor" v-for="field in fields" v-html="field"></th>
          </tr>
          </thead>
          <tbody>
          <tr class="ph-indexRow" v-for="datum in data">
            <td class="ph-indexTd" v-for="column in datum" v-html="column"></td>
          </tr>
          </tbody>
        </table>
      </section>
    </section>
  </transition>
</template>

<script lang="ts">
  import Vue from 'vue'
  import axios, {CancelTokenSource, CancelToken, AxiosPromise, AxiosError} from 'axios';

  //CodeIgniterが提供する変数
  declare var csrf_key: string;
  declare var csrf_value: string;
  declare var site_url: string;

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
      key: {
        type: String,
        required: true,
        default: ''
      }
    },
    data () {
      return {
        loading: false,
        source: null,
        data: {},
        fields: {}
      }
    },
    watch: {
      '$route' (to, from)
      {
        this.renderData();
      }
    },
    mounted: function() {
      this.renderData();
    },
    methods: {
      /**
       * 通信中のAjax送信が存在したらそれをキャンセルし、新たなトークンを返す
       * @return {CancelTokenSource}
       */
      abort: function (): CancelToken
      {
        //もし通信中だったらその通信をキャンセルする
        if (this.source !== null)
        {
          this.source.cancel();
          this.source = null;
        }

        //axiosのキャンセルトークンを登録
        const CancelToken = axios.CancelToken;
        this.source = CancelToken.source();
        return this.source.token;
      },
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
       * initApiにセットされたURLを叩いて初期表示のためのデータを取得する
       * 結果がAxiosPtomiseとして返る
       * @return {AxiosPromise}
       */
      getData: function (): AxiosPromise
      {
        //POSTに渡すパラメータ
        let params: URLSearchParams = new URLSearchParams();
        params.append(csrf_key, csrf_value);

        //通信を試みる
        return axios.post(site_url+this.$props.initApi, params, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          cancelToken: this.abort()
        });
      },
      /**
       * データを取得し、描写する
       */
      renderData: function()
      {
        //loading中アイコンとダミーリストを表示する
        this.loading = true;

        //ダミーテーブルの描写
        this.renderDummyList(10, 10);

        //データの入手
        this.getData()
          .then((response: {data: {fields: Array<string>, data: Array<{[key: string]: string|null}>}}) =>
          {
            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;

            this.fields = response.data.fields;
            this.data = response.data.data;
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