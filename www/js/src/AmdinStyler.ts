/**
 * 管理画面のスタイルをJSで制御する時の汎用クラス
 */
export class AmdinStyler{

  constructor()
  {}

  /**
   * 渡されたElement型がHTMLElement型のであることを保証するtype guard
   * @param element
   * @return {element is HTMLElement}
   */
  private isHTMLElement(element: any): element is HTMLElement
  {
    return element instanceof HTMLElement;
  }

  /**
   * window or document の内、heightが大きいモノに合わせて
   * targetClassNamesに存在するクラス名を持つ要素の高さを合わせる
   * @param {Array<string>} targetClassNames
   */
  public initHeightStyle(targetClassNames: Array<string>)
  {
    const height: number = Math.max.apply(null, [document.body.clientHeight , document.body.scrollHeight, document.documentElement.scrollHeight, document.documentElement.clientHeight]);
    const heightPx: string = height+'px';

    targetClassNames.forEach((className: string) =>
    {
      const targets: HTMLCollection = document.getElementsByClassName(className);

      for (let i = 0; i < targets.length; i++)
      {
        const target: Element = targets.item(i);
        if (this.isHTMLElement(target))
        {
          target.style.height = heightPx;
        }
      }
    });
  }

}