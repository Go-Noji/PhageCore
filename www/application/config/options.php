<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ここに書かれた設定はoptionsテーブルの同名key_nameの値を上書きします。
 * 更に、上書きされた設定は管理画面での設定が不可になります。
 */

//一つも設定が無かった場合この設定ファイルが正常にロードされないのを回避
if ( ! isset($config))
{
  $config[''] = '';
}
