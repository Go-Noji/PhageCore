<template>
  <transition name="edit">
    <section class="ph-innerWrapper">
      {{$route.params.id}}
      <ul>
        <li v-for="(datum, key, index) in data">
          <p>{{fields[key].label}}</p>
          <div v-if="fields[key].type === 'select'">
            <label>
              <select :name="key">
                <template v-for="(optionKey, optionValue) in fields[key].options">
                  <option v-if="optionValue === datum" :value="optionValue" selected>{{optionKey}}</option>
                  <option v-else :value="optionValue">{{optionKey}}</option>
                </template>
              </select>
            </label>
          </div>
          <div v-else-if="fields[key].type === 'radio'">
            <ul>
              <li v-for="(optionKey, optionValue) in fields[key].options">
                <label>
                  <p>{{optionKey}}</p>
                  <input v-if="optionValue === datum" :name="key" :value="optionValue" type="radio" checked>
                  <input v-else :name="key" :value="optionValue" type="radio">
                </label>
              </li>
            </ul>
          </div>
          <div v-else-if="fields[key].type === 'checkbox'">
            <ul>
              <li v-for="(optionKey, optionValue) in fields[key].options">
                <label>
                  <p>{{optionKey}}</p>
                  <input v-if="optionValue === datum" :name="key" :value="optionValue" type="checkbox" checked>
                  <input v-else :name="key" :value="optionValue" type="checkbox">
                </label>
              </li>
            </ul>
          </div>
          <div v-else>
            <label>
              <input :name="key" :value="datum" :type="fields[key].type">
            </label>
          </div>
        </li>
      </ul>
    </section>
  </transition>
</template>

<script lang="ts">
  import Vue from 'vue'
  import {AxiosError} from 'axios';

  //initAPIから取得できる'fields'のデータ構造
  //キー名がDBのフィールド名で値は描写に使われる人間用の名前
  interface fieldsInterface {
    [key: string]: {control: boolean, label: string, type: string, options: {[key: string]: string}}
  }

  //initAPIから取得できる'data'のデータ構造
  //キー名がDBのカラム名、値がデータそのもの
  interface dataInterface {
    [key: string]: string|null
  }

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
        //バックエンドと通信中の時にだけtrueになる
        loading: false,

        //DBから取得してきたフィールド名配列
        //中身はオブジェクトで、{フィールド名: 人間用の表示名}という形になっている
        fields: {},

        //DBから取得してきたデータ配列
        //中身はオブジェクトで、{フィールド名: データ}という形になっている
        data: []
      }
    },
    watch: {
      '$route' (to, from)
      {
        this.renderEdit();
      }
    },
    mounted: function()
    {
      this.renderEdit();
    },
    methods: {
      renderEdit: function()
      {
        //loading中アイコンとダミーリストを表示する
        this.loading = true;

        this.$store.dispatch('connectAPI', {api: this.$props.initApi, data: {arguments: [this.$route.params.id]}})
          .then(() =>
          {
            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;

            //データをVuexから取得
            const data: {
              fields: fieldsInterface,
              data: dataInterface[]
            } = this.$store.getters.getData(['fields','data']);

            this.data = data.data;
            this.fields = data.fields;
          })
          .catch((data: AxiosError) =>
          {
            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;
          });
      }
    }
  });
</script>

<style scoped type="text/scss"></style>