import {Module} from 'vuex';
import {AdminState} from '../interface';

/**
 * EditModuleのためのInterface
 */
interface EditData {
  api: string,
  value: string,
  connect: boolean,
  success: boolean
}
interface EditState {
  id: number,
  data: {
    [key: string]: EditData
  }
}

//編集画面ごとの更新用モジュール
const editModule: Module<EditState, AdminState> = {
  namespaced: true,
  state: {
    id: 0,
    data: {}
  },
  mutations: {
    /**
     * 編集する対象のIDと各項目データをセットする
     * @param state
     * @param payload
     */
    init(state: EditState, payload: {id: number, data: {[key: string]: EditData}})
    {
      state.id = payload.id;
      state.data = payload.data;
    },

    /**
     * 項目の値を変更する
     * @param state
     * @param payload
     */
    change(state: EditState, payload: {key: string, value: string})
    {
      state.data[payload.key].value = payload.value;
    },

    /**
     * 特定の項目を更新キューに入れる
     * payloadのkeyが空文字だった場合は全ての項目がキューに入る
     * @param state
     * @param payload
     */
    queue(state: EditState, payload: {key: string})
    {
      //特定の項目を指定されている場合
      if (payload.key !== '')
      {
        state.data[payload.key].connect = true;
        return;
      }

      //全ての項目をキューに入れる
      for(let k of Object.keys(state.data))
      {
        state.data[k].connect = true;
      }
    },

    /**
     * 更新成功時に特定の項目のsuccessをtrueに、errorを空文字にする
     * @param state
     * @param payload
     */
    then(state: EditState, payload: {key: string})
    {
      state.data[payload.key].success = true;
      state.data[payload.key].connect = false;
    },

    /**
     * 更新失敗時に特定項目のsuccessをfalseにし、errorを登録する
     * @param state
     * @param payload
     */
    catch(state: EditState, payload: {key: string, error: string})
    {
      state.data[payload.key].success = false;
      state.data[payload.key].connect = false;
    }
  },
  actions: {
    /**
     * 現在キューに入っている項目を全てバックエンドと通信して更新する
     * 通信は親ModuleであるAdminModuleのconnectをdispatchして行われる
     * 成功・失敗に関係なく通信済みの項目はキューから除外される
     * @param commit
     * @param state
     */
    submit({commit, state, getters})
    {
      //非同期通信のためのタスク配列を用意
      const task = [];

      //項目ごとに処理
      for (let k of Object.keys(state.data))
      {
        //対象が更新対象でない場合は飛ばす
        if ( ! state.data[k].connect)
        {
          continue;
        }

        //非同期タスクの追加
        //親Moduleのconnectをdispatchする
        const data: {data: {[key: string]: string}, segments: Array<number>} = {data: {}, segments: [state.id]};
        data.data[k] = state.data[k].value;
        task.push(new Promise((resolve, reject) => {
          this.dispatch('connect/connectAPI', {api: state.data[k].api, data: data}, {root: true})

          //更新成功
            .then(() =>
            {
              commit('then', {key: k});
              resolve();
            })

            //更新失敗
            .catch(() =>
            {
              commit('catch', {key: k});
              reject();
            });
        }));
      }

      //全ての非同期タスクを実行する
      return Promise.all(task);
    }
  }
};

export default editModule;