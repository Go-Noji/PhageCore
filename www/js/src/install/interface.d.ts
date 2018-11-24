/**
 * Vuexのstate
 */
export interface InstallStoreInterface{
  values: {[key: string]: string|null}
}

/**
 * Vuexのdefinitionミューテーションに対するペイロード
 */
export interface DefinitionPayload {
  key: string
}

/**
 * VuexのsetValueミューテーションに対するペイロード
 */
export interface SetValuePayload {
  key: string,
  value: string
}

//バリデーションを行ったときに返ってくるデータ
//validationにバリデーションのエラーメッセージが入る
export interface ValidationData{
  validation: {[key: string]: string}
}

/**
 * eventターゲットのラップ用
 */
export interface HTMLElementEvent<T extends HTMLElement> extends Event{
  target: T
}