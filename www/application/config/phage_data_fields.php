<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/*---options---*/

/*
|--------------------------------------------------------------------------
| Definition of `options` database
|--------------------------------------------------------------------------
|
| options テーブルと実際の表示の相互変換のために定義された配列
| label: 実際にフロントエンドへ表示される項目名
| control: 管理画面から編集可能なのかどうかのbool値
| type: フロントエンドの編集UI('text', 'password', 'number', 'select', 'radio', 'checkbox', 'textarea', 'file')
| options: typeが'select', 'radio', 'checkbox'の場合に有効な選択肢
|
*/
$config['options_data_fields'] = array(
  'id' => array(
    'label' => 'id',
    'control' => FALSE,
    'type' => 'text',
    'options' => array()
  ),
  'key_name' => array(
    'label' => '設定名',
    'control' => TRUE,
    'type' => 'text',
    'options' => array()
  ),
  'value' => array(
    'label' => '設定値',
    'control' => TRUE,
    'type' => 'text',
    'options' => array()
  ),
  'description' => array(
    'label' => '説明',
    'control' => TRUE,
    'type' => 'textarea',
    'options' => array()
  )
);
