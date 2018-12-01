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
            <AdminEditSubmit :fields="fields" :name="name" :field="key" :data="data[key]" :connectAll="connectAll"></AdminEditSubmit>
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
            <AdminEditSubmit :fields="fields" :name="name" :field="key" :data="data[key]" :connectAll="connectAll"></AdminEditSubmit>
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
            <AdminEditSubmit :fields="fields" :name="name" :field="key" :data="data[key]" :connectAll="connectAll"></AdminEditSubmit>
          </div>
          <div v-else-if="fields[key].type === 'textarea'" class="ph-inputWrapper">
            <label>
              <textarea :name="key" :type="fields[key].type" v-model="data[key]" class="ph-textarea"></textarea>
            </label>
            <AdminEditSubmit :fields="fields" :name="name" :field="key" :data="data[key]" :connectAll="connectAll"></AdminEditSubmit>
          </div>
          <div v-else class="ph-inputWrapper">
            <label>
              <input :name="key" :type="fields[key].type" v-model="data[key]" class="ph-input">
            </label>
            <AdminEditSubmit :fields="fields" :name="name" :field="key" :data="data[key]" :connectAll="connectAll"></AdminEditSubmit>
          </div>
        </li>
      </ul>
      <div>
        <button @click="submitAll" required="required" class="ph-submit ph-adjustmentMt15" type="button">全て更新<i class="fas fa-sync-alt ph-adjustmentMl5"></i></button>
      </div>
    </section>
  </transition>
</template>

<script lang="ts">
  import Vue from 'vue'
  import {AxiosError} from 'axios';
  import AdminEditSubmit from './AdminEditSubmit.vue';
  import {EditData} from "./interface";

  export default Vue.extend({
    components: {
      AdminEditSubmit
    },
    props: {
      initApi: {
        type: String,
        required: true,
        default: ''
      },
      name: {
        type: String,
        required: true
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
        data: {},

        //全ての項目を更新している間のみtrueになる
        connectAll: false
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
      /**
       * 描画する
       */
      renderEdit: function()
      {
        //loading中アイコンとダミーリストを表示する
        this.loading = true;

        this.$store.dispatch('connect/connectAPI', {api: this.$props.initApi, data: {segments: [this.$route.params.id]}})
          .then(() =>
          {
            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;

            //VuexのEditModuleを初期化するため各項目の情報を集める
            const editData: {[key: string]: EditData} = {};

            //connectingプロパティを仕込みつつthis.fieldsに情報を追加
            for (let k of Object.keys(this.$store.state.connect.data.fields))
            {
              //描画情報をセット
              this.$set(this.fields, k, this.$store.state.connect.data.fields[k]);
              this.$set(this.fields[k], 'connecting', false);
            }

            //this.dataの追加
            for (let k of Object.keys(this.$store.state.connect.data.data))
            {
              //描画情報をセット
              this.$set(this.data, k, this.$store.state.connect.data.data[k]);

              //もし変更不可なフィールドだったらVuexに登録しない
              if ( ! this.$store.state.connect.data.fields[k].control)
              {
                continue;
              }

              //Vuex情報を追加
              editData[k] = {
                api: 'api/admin/mutation/'+this.name+'/set',
                value: this.$store.state.connect.data.data[k],
                connect: false,
                success: false,
                error: ''
              }
            }

            //VuexのEditModuleを初期化
            this.$store.commit('edit/init', {id: this.$route.params.id, data: editData});
          })
          .catch((data: AxiosError) =>
          {
            //loading中アイコンとダミーリストを非表示にする
            this.loading = false;
          });
      },

      /**
       * 全項目を更新する
       */
      submitAll: function()
      {
        //更新フラグがtrueの間は何もしない
        if (this.connectAll)
        {
          return;
        }

        //更新中フラグをtrueにする
        this.connectAll = true;

        //全ての項目を更新キューにcommitする
        this.$store.commit('edit/queue', {key: ''});

        //変更
        this.$store.dispatch('edit/submit')
          .then(() =>
          {
            //更新中フラグをfalseに戻す
            this.connectAll = false;
          })
          .catch(() =>
          {
            //更新中フラグをfalseに戻す
            this.connectAll = false;
          });
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