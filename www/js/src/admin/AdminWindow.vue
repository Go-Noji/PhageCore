<template>
  <section class="ph-adminWindow">
    <h2 class="ph-adminWindowTitle">コンテンツ</h2>
    <section class="ph-innerWrapper">
      <h3>コンテンツ一覧</h3>
      <table class="ph-index">
        <thead class="ph-indexHead">
        <tr class="ph-indexRow ph-indexHeadRow">
          <th class="ph-indexTh ph-reverseColor" :class="thClass" v-for="field in fields" v-html="field"></th>
        </tr>
        </thead>
        <tbody>
        <tr class="ph-indexRow" v-for="content in contents">
          <td class="ph-indexTd" :class="tdClass" v-for="column in content" v-html="column"></td>
        </tr>
        </tbody>
      </table>
    </section>
  </section>
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
      initApi: {
        type: String,
        required: true,
        default: ''
      }
    },
    data () {
      return {
        loading: false,
        source: null,
        contents: {},
        fields: {}
      }
    },
    watch: {
      '$route': 'getData'
    },
    computed: {
      //ダミーリストの表示クラス
      thClass: function ()
      {
        return {
          'ph-dummyTH': this.loading
        }
      },
      tdClass: function ()
      {
        return {
          'ph-dummyTD': this.loading
        }
      },
    },
    mounted: function() {
      //loading中アイコンとダミーリストを表示する
      this.loading = true;

      //ダミーテーブルの描写
      this.renderDummyList(10, 10);

      //データの入手
      this.getData()
        .then((response: {data: {fields: Array<string>, contents: Array<{[key: string]: string|null}>}}) =>
        {
          //loading中アイコンとダミーリストを非表示にする
          this.loading = false;

          this.fields = response.data.fields;
          this.contents = response.data.contents;
        })
        .catch((data: AxiosError) =>
        {
          //loading中アイコンとダミーリストを非表示にする
          this.loading = false;

          console.log(data);
        });
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
        const fields: string[] = new Array<string>(columnNumber).fill('&nbsp;');

        //空データ配列の一行分を作成
        const content: {[key: string]: string|null} = {};
        Array.prototype.forEach.call(fields, (field: string, index: number) =>
        {
          content[field+index] = '&nbsp;';
        });

        //空データの作成
        const contents: {[key: string]: string|null}[] = new Array<{[key: string]: string|null}>(count).fill(content);

        console.log(fields)
        console.log(contents)
        this.fields = fields;
        this.contents = contents;
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
      }
    }
  });
</script>

<style scoped type="text/scss">
  @keyframes dummyAnimation{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
  }

  @mixin dummyCell{
    background-size: 600% 600%;
    animation: dummyAnimation 1s ease-in infinite;
  }

  .ph-dummyTH{
    background: linear-gradient(79deg, #0099a2, #e5e5e5, #0099a2);
    @include dummyCell;
  }

  .ph-dummyTD{
    background: linear-gradient(79deg, #aaa, #e5e5e5, #aaa);
    @include dummyCell;
  }
</style>