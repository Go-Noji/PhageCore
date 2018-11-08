<template>
  <transition name="ph-transition-edit">
    <section class="ph-editWrapper">
      <ul>
        <li v-for="(datum, key) in data">
          <p v-if="fields[key].control" class="ph-inputHeader">{{fields[key].label}}</p>
          <div v-if=" ! fields[key].control"></div>
          <div v-else-if="fields[key].type === 'select'" class="ph-inputWrapper">
            <label class="ph-selectWrapper">
              <select :name="key" class="ph-select">
                <template v-for="(optionKey, optionValue) in fields[key].options">
                  <option v-model="data[key]" :value="optionValue">{{optionKey}}</option>
                </template>
              </select>
            </label>
            <AdminEditSubmit :fields="fields" :name="key" :data="data[key]" initApi=""></AdminEditSubmit>
          </div>
          <div v-else-if="fields[key].type === 'radio'" class="ph-inputWrapper">
            <ul>
              <li v-for="(optionKey, optionValue) in fields[key].options">
                <label>
                  <p class="ph-inputHeader">{{optionKey}}</p>
                  <input :name="key" :value="optionValue" v-model="data[key]" class="ph-input" type="radio">
                </label>
              </li>
            </ul>
            <AdminEditSubmit :fields="fields" :name="key" :data="data[key]" initApi=""></AdminEditSubmit>
          </div>
          <div v-else-if="fields[key].type === 'checkbox'" class="ph-inputWrapper">
            <ul>
              <li v-for="(optionKey, optionValue) in fields[key].options">
                <label>
                  <p class="ph-inputHeader">{{optionKey}}</p>
                  <input :name="key" :value="optionValue" v-model="data[key]" class="ph-input" type="checkbox">
                </label>
              </li>
            </ul>
            <AdminEditSubmit :fields="fields" :name="key" :data="data[key]" initApi=""></AdminEditSubmit>
          </div>
          <div v-else-if="fields[key].type === 'textarea'" class="ph-inputWrapper">
            <label>
              <textarea :name="key" :type="fields[key].type" v-model="data[key]" class="ph-textarea"></textarea>
            </label>
            <AdminEditSubmit :fields="fields" :name="key" :data="data[key]" initApi=""></AdminEditSubmit>
          </div>
          <div v-else class="ph-inputWrapper">
            <label>
              <input :name="key" :type="fields[key].type" v-model="data[key]" class="ph-input">
            </label>
            <AdminEditSubmit :fields="fields" :name="key" :data="data[key]" initApi=""></AdminEditSubmit>
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
        data: {}
      }
    },
    watch: {
      '$route' (to, from)
      {
        this.renderEdit();
      }
    },
    mounted: async function()
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
              data: dataInterface
            } = this.$store.getters.getData(['fields','data']);

            //connectingプロパティを仕込みつつthis.fieldsに情報を追加
            for (let k of Object.keys(data.fields))
            {
              this.$set(this.fields, k, data.fields[k]);
              this.$set(this.fields[k], 'connecting', false);
            }

            //this.dataの追加
            for (let k of Object.keys(data.data))
            {
              this.$set(this.data, k, data.data[k]);
            }
          })
          .catch((data: AxiosError) =>
          {
            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;
          });
      },

      changeValue: function()
      {

      }
    }
  });
</script>

<style scoped type="text/scss">
  .ph-transition-edit-enter-active, .ph-transition-edit-leave-active {
    transition: opacity .15s ease-in-out, width .15s ease;
  }
  .ph-transition-edit-enter, .ph-transition-edit-leave-to{
    opacity: 0;
    width: 0;
  }
</style>