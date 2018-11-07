<template>
  <transition name="edit">
    <section class="ph-editWrapper">
      <ul>
        <li v-for="(datum, key) in data">
          <p v-if="fields[key].control" class="ph-inputHeader">{{fields[key].label}}</p>
          <div v-if=" ! fields[key].control"></div>
          <div v-else-if="fields[key].type === 'select'" class="ph-inputWrapper">
            <label class="ph-selectWrapper">
              <select :name="key" class="ph-select">
                <template v-for="(optionKey, optionValue) in fields[key].options">
                  <option v-if="optionValue === datum" :value="optionValue" selected>{{optionKey}}</option>
                  <option v-else :value="optionValue">{{optionKey}}</option>
                </template>
              </select>
            </label>
            <AdminEditSubmit :fields="fields" :name="key" initApi=""></AdminEditSubmit>
          </div>
          <div v-else-if="fields[key].type === 'radio'" class="ph-inputWrapper">
            <ul>
              <li v-for="(optionKey, optionValue) in fields[key].options">
                <label>
                  <p class="ph-inputHeader">{{optionKey}}</p>
                  <input v-if="optionValue === datum" :name="key" :value="optionValue" class="ph-input" type="radio" checked>
                  <input v-else :name="key" :value="optionValue" class="ph-input" type="radio">
                </label>
              </li>
            </ul>
            <AdminEditSubmit :fields="fields" :name="key" initApi=""></AdminEditSubmit>
          </div>
          <div v-else-if="fields[key].type === 'checkbox'" class="ph-inputWrapper">
            <ul>
              <li v-for="(optionKey, optionValue) in fields[key].options">
                <label>
                  <p class="ph-inputHeader">{{optionKey}}</p>
                  <input v-if="optionValue === datum" :name="key" :value="optionValue" class="ph-input" type="checkbox" checked>
                  <input v-else :name="key" :value="optionValue" class="ph-input" type="checkbox">
                </label>
              </li>
            </ul>
            <AdminEditSubmit :fields="fields" :name="key" initApi=""></AdminEditSubmit>
          </div>
          <div v-else-if="fields[key].type === 'textarea'" class="ph-inputWrapper">
            <label>
              <textarea :name="key" :type="fields[key].type"v-html="datum" class="ph-textarea"></textarea>
            </label>
            <AdminEditSubmit :fields="fields" :name="key" initApi=""></AdminEditSubmit>
          </div>
          <div v-else class="ph-inputWrapper">
            <label>
              <input :name="key" :value="datum" :type="fields[key].type" class="ph-input">
            </label>
            <AdminEditSubmit :fields="fields" :name="key" initApi=""></AdminEditSubmit>
          </div>
        </li>
      </ul>
    </section>
  </transition>
</template>

<script lang="ts">
  import Vue from 'vue'
  import {AxiosError} from 'axios';
  import AdminEditSubmit from './AdminEditSubmit.vue';

  //initAPIから取得できる'fields'のデータ構造
  //キー名がDBのフィールド名で値は描写に使われる人間用の名前
  interface fieldsInterface {
    [key: string]: {control: boolean, label: string, type: string, options: {[key: string]: string}, connecting: boolean}
  }

  //initAPIから取得できる'data'のデータ構造
  //キー名がDBのカラム名、値がデータそのもの
  interface dataInterface {
    [key: string]: string|null
  }

  export default Vue.extend({
    components: {
      AdminEditSubmit
    },
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

            //connectingプロパティを仕込みつつthis.fieldsに情報を追加
            for (let k of Object.keys(data.fields))
            {
              this.$set(this.fields, k, data.fields[k]);
              this.$set(this.fields[k], 'connecting', false);
            }

            //this.dataの追加
            this.data = data.data;
          })
          .catch((data: AxiosError) =>
          {
            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;
          });
      },
    }
  });
</script>

<style scoped type="text/scss"></style>