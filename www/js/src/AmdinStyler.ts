/**
 * 管理画面のスタイルをJSで制御する時の汎用クラス
 */
export class AmdinStyler{

  /**
   * ページの縦幅
   * document.body.clientHeight,
   * document.body.scrollHeight,
   * document.documentElement.scrollHeight,
   * document.documentElement.clientHeight
   * の内、最大のものが_setPageHeightによって適用される
   * 初期値はnull
   */
  private pageHeight: number|null;

  /**
   * 初期値のセット
   */
  constructor()
  {
    this.pageHeight = null;
  }

  /**
   * 渡されたElement型がHTMLElement型のであることを保証するtype guard
   * @param element
   * @return {element is HTMLElement}
   */
  private _isHTMLElement(element: any): element is HTMLElement
  {
    return element instanceof HTMLElement;
  }

  /**
   * window or document の内、heightが最大のものをpageHeightにセットする
   * @private
   */
  private _setPageHeight()
  {
    this.pageHeight = Math.max.apply(null, [document.body.clientHeight , document.body.scrollHeight, document.documentElement.scrollHeight, document.documentElement.clientHeight]);
  }

  /**
   * 指定されたtargetClassNamesをclassに持つ要素のheightをheightにする
   * @param {Array<string>} targetClassNames
   * @param {number} height
   * @private
   */
  private _setHeight(targetClassNames: Array<string>, height: number)
  {
    //pxを付ける
    const heightPx: string = height+'px';

    //対象クラスに対してheightを適用
    targetClassNames.forEach((className: string) =>
    {
      const targets: HTMLCollection = document.getElementsByClassName(className);

      for (let i = 0; i < targets.length; i++)
      {
        const target: Element = targets.item(i);
        if (this._isHTMLElement(target))
        {
          target.style.height = heightPx;
        }
      }
    });
  }

  /**
   * window or document の内、heightが大きいモノに合わせて
   * targetClassNamesに存在するクラス名を持つ要素の高さを合わせる
   * adjustmentを指定すると適用されるheightがpageHeight+adjustmentとなる
   * @param {Array<string>} targetClassNames
   * @param {number} adjustment
   */
  public initHeightStyle(targetClassNames: Array<string>, adjustment: number = 0)
  {
    //height値の算出
    if (this.pageHeight === null)
    {
      this._setPageHeight();
    }

    //適用
    this._setHeight(targetClassNames, this.pageHeight + adjustment);
  }

}