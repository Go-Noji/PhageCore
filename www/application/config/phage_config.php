<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/*---admin---*/

/*
|--------------------------------------------------------------------------
| Key for login saved in session
|--------------------------------------------------------------------------
|
| $_SESSIONの中に保存されるログインのためのキー
| 変更すると現在のログインが一切合切外れる
|
*/
$config['admin_login_key'] = 'login_id';

/*
|--------------------------------------------------------------------------
| Maximum length of tab of management page
|--------------------------------------------------------------------------
|
| 管理画面で使用できるタブの最大値
|
*/
$config['admin_tab_max'] = 4;

/*
|--------------------------------------------------------------------------
| Settings to display
|--------------------------------------------------------------------------
|
| 管理画面の「設定」で表示する設定
|
*/
$config['admin_settings'] = array(
  'site_name' => array(
    'label' => 'サイト名',
    'type' => 'text',
    'multiple' => FALSE,
    'value' => '',
    'placeholder' => '',
    'class' => ''
  ),
  'enable_dashboard' => array(
    'label' => 'ダッシュボードを有効にする',
    'type' => 'checkbox',
    'multiple' => FALSE,
    'value' => '1',
    'placeholder' => '',
    'class' => ''
  )
);



/*---types---*/

/*
|--------------------------------------------------------------------------
| Type of posts
|--------------------------------------------------------------------------
|
| 投稿のタイプ
| 通常の時系列投稿や固定ページ、またはただのメモ等を設定できる
| 例えばtype=0のレコードを「固定ページ」そして扱いたい場合は
| 配列の0番目に固定ページに関する配列を設定する
| 'label'は投稿タイプの名前
| 'metadata'は投稿タイプに少なくとも一つは存在することが保証される
| metadataテーブルの値
| 'metadata'の内部、
| 'field'はmetadataテーブルのkey_nameとして挿入される文字列（必須）
| 'form'view/formディレクトリにある「○○○_form.php」の○○○を入力
| 'validation'はconfig/form_validationにある「form_validation.php」の検証ルールセット名
|
*/
$config['content_type'] = array(
  array(
    'label' => 'post',
    'metadata' => array(
      array(
        'field' => 'title',
        'form' => 'text',
        'validation' => 'post_title'
      ),
      array(
        'field' => 'excerpt',
        'form' => 'textarea',
        'validation' => 'post_excerpt'
      ),
      array(
        'field' => 'body',
        'form' => 'html',
        'validation' => 'post_body'
      ),
      array(
        'field' => 'thumbnail',
        'form' => 'media',
        'validation' => 'post_media'
      )
    )
  ),
  array(
    'label' => 'static',
    'metadata' => array(
      array(
        'field' => 'title',
        'form' => 'text',
        'validation' => 'post_title'
      ),
      array(
        'field' => 'body',
        'form' => 'html',
        'validation' => 'post_body'
      ),
      array(
        'field' => 'thumbnail',
        'form' => 'media',
        'validation' => 'post_media'
      )
    )
  )
);

/*
|--------------------------------------------------------------------------
| Type of attribute for posts
|--------------------------------------------------------------------------
|
| カテゴリーやタグ等の属性配列
| attributeテーブルにおけるtypeカラムの設定をする
| 例えばtype=0のレコードを「カテゴリー」そして扱いたい場合は
| 配列の0番目に'カテゴリー'という文字列を挿入する
*/
$config['attribute_type'] = array('カテゴリー', 'タグ');



/*---cache---*/

/*
|--------------------------------------------------------------------------
| Cache json flg
|--------------------------------------------------------------------------
|
| JSONを先に作成しておき、フロントからのリクエストに対し
| 極力DBにアクセスさせないようにする設定
| フロントの動作は早くなるが、管理画面側の更新に負荷がかかる
|
*/
$config['enable_pre_json'] = TRUE;



/*---comment---*/

/*
|--------------------------------------------------------------------------
| Enable Comment
|--------------------------------------------------------------------------
|
| コメント機能のon/off設定
| Falseにすると一切のコメント機能が使用できなくなる
|
*/
$config['enable_comment'] = FALSE;

/*
|--------------------------------------------------------------------------
| Reply restriction that only the administrator can reply
|--------------------------------------------------------------------------
|
| TRUEにするとコメントに対して返信できるのが管理人だけになる
| 管理人の返信に対してはこの設定に関わらず返信可能
|
*/
$config['enable_reply_restriction'] = TRUE;



/*image*/

/*
|--------------------------------------------------------------------------
| Alternate image when no image is set
|--------------------------------------------------------------------------
|
| 画像が見つからなかった時の代替イメージ
|
*/
$connfig['no_image_path'] = '';

/*
|--------------------------------------------------------------------------
| Setting whether to create thumbnails
|--------------------------------------------------------------------------
|
| メディアに画像を追加したときにサムネイルを作るかどうかの設定
|
*/
$connfig['enable_create_thumb'] = TRUE;

/*
|--------------------------------------------------------------------------
| variation of thumbnail
|--------------------------------------------------------------------------
|
| サムネイルのバリエーション設定
|
*/
$connfig['thumb_sizes'] = array(
  'big' => 1000,
  'middle' => 640,
  'small' => 50
);
