<template>
  <section class="ph-adminWindow">
    <h2 class="ph-adminWindowTitle">コンテンツ</h2>
    <section class="ph-innerWrapper">
      <h3>コンテンツ一覧</h3>
      <table class="ph-index">
        <thead class="ph-indexHead">
        <tr class="ph-indexRow ph-indexHeadRow">
          <th class="ph-indexTh ph-reverseColor" v-for="field in fields">{{field}}</th>
        </tr>
        </thead>
        <tbody>
        <tr class="ph-indexRow" v-for="content in contents">
          <td v-for="column in content" class="ph-indexTd">{{column}}</td>
        </tr>
        </tbody>
      </table>
    </section>
  </section>
</template>

<script lang="ts">
  import Vue from 'vue'
  import axios, {CancelTokenSource, AxiosPromise, AxiosError} from 'axios';

  //CodeIgniterが提供する変数
  declare var csrf_key: string;
  declare var csrf_value: string;
  declare var site_url: string;

  //通信中のaxiosキャンセルトークン
  let source: CancelTokenSource|null = null;

  export default Vue.extend({
    props: {
      api: {
        type: String,
        required: true,
        default: ''
      }
    },
    data () {
      return {
        contents: {},
        fields: {}
      }
    },
    watch: {},
    mounted: function() {
      //データの入手
      this.getData()
        .then((response: {data: {fields: {}, contents: {}}}) =>
        {
          this.fields = response.data.fields;
          this.contents = response.data.contents;
        })
        .catch((data: AxiosError) =>
        {
          console.log(data);
        });
    },
    methods: {
      getData: function (): AxiosPromise
      {
        //もし通信中だったらその通信をキャンセルする
        if (source !== null)
        {
          source.cancel();
          source = null;
        }

        //axiosのキャンセルトークンを登録
        const CancelToken = axios.CancelToken;
        source = CancelToken.source();

        //POSTに渡すパラメータ
        let params: URLSearchParams = new URLSearchParams();
        params.append(csrf_key, csrf_value);

        //通信を試みる
        return axios.post(site_url+this.$props.api, params, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          cancelToken: source.token
        });
      }
    }
  });
</script>

<style scoped></style>