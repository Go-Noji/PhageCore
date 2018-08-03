/**
 * チェックボックス関連の動作を司るクラス
 */
export class Checkbox{

  private targetClassName: string;

  constructor()
  {}

  /**
   * イベントターゲットのcheckedと
   * this.targetClassNameをclassに持つエレメントのcheckedを同期させる
   * @param e
   * @private
   */
  private _changeAll(e: Event)
  {
    const target: HTMLInputElement = <HTMLInputElement>e.target;
    if (target.checked === undefined)
    {
      return;
    }

    const sync: boolean = target.checked;

    const checkboxes: HTMLCollection = document.getElementsByClassName(this.targetClassName);
    Array.prototype.forEach.call(checkboxes, (checkbox: HTMLInputElement) =>
    {
      if (target.checked !== undefined)
      {
        checkbox.checked = sync;
      }
    });
  }

  /**
   * 対象となるチェックボックスが持つクラス名をセットする
   * @param targetClassName
   */
  public setTargetClassName(targetClassName: string)
  {
    //チェックの対象となるクラスの定義
    this.targetClassName = targetClassName;
  }

  //triggerClassNameを持つチェックボックスのchangeイベントに_changeAll関数を紐づける
  public registerAllCheck(triggerClassName: string)
  {
    //トリガーとなるボックスにイベントを紐づける
    const triggerCheckBoxes: HTMLCollection = document.getElementsByClassName(triggerClassName);
    Array.prototype.forEach.call(triggerCheckBoxes, (triggerCheckBox: HTMLElement) =>
    {
      triggerCheckBox.addEventListener('change', (e) => {this._changeAll(e)}, false);
    });
  }

}