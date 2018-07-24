import Vue from 'vue';
import axios from 'axios';
import {AmdinStyler} from "../AmdinStyler";

//CodeIgniterが提供する変数
declare var csrf_key: string;
declare var csrf_value: string;
declare var site_url: string;

(() =>
{

  //スタイル調整クラスのインスタンス化
  const adminStyler = new AmdinStyler();

  //高さを合わせたいクラス名(複数)
  const fullHeightClassNames: Array<string> = ['pc-js-fullHeight'];
  const contentsClassNames: Array<string> = ['pc-js-adminSidebar', 'pc-js-adminArea'];

  window.onload = () =>
  {
    //height合わせ
    adminStyler.initHeightStyle(fullHeightClassNames);
    adminStyler.initHeightStyle(contentsClassNames, - document.querySelector('.pc-js-adminHeader').getBoundingClientRect().height);
  }

  //画面リサイズによるheight合わせ
  window.addEventListener('resize', () =>
  {
    adminStyler.initHeightStyle(fullHeightClassNames);
    adminStyler.initHeightStyle(contentsClassNames, - document.querySelector('.pc-js-adminHeader').getBoundingClientRect().height);
  }, false);
})();
