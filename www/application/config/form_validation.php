<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 */

//オプション
$config['options_key_name'] = array(
  'field' => 'key_name',
  'label' => '設定名',
  'rules' => 'required|max_length[256]|alpha_dash',
  'errors' => array(
    'required' => '{field}は必須です',
    'max_length' => '{field}が長すぎます',
    'alpha_dash' => '{field}に半角英数字、_、-、以外が含まれています'
  )
);
