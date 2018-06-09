<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
  <meta name="description" content="Phage Core インストール">
  <title>Phage Core インストール</title>
  <link rel="icon" href="images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="images/apple-touch-icon.png" sizes="180x180">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-TileImage" content="/favicon/mstile-144x144.png">
  <meta name="theme-color" content="#007386">
</head>
<body class="pc-body">
<div class="pc-wrapper">
  <div class="pc-between">
    <section class="pc-image_back">
      <p class="pc-paragraph"><img class="ps-install_logo" src="<?php echo site_url('images/logo.png'); ?>" alt="Phage Core ロゴ"></p>
      <h1>Phage Core　インストール</h1>
      <p class="pc-paragraph">Phage Codeのインストールを行います。</p>
      <p class="pc-paragraph">必要情報を入力して「インストール」ボタンを押して下さい。</p>
    </section>
    <section class="pc-form_wrapper">
      <h2>必要情報入力</h2>
      <?php echo form_open() ?>
      <talbe>
        <tbody>
        <tr>
          <td>
            <h3 class="pc-sub_title">管理者名</h3>
            <p class="pc-paragraph">管理者名を256文字以下で設定してください。</p>
            <p class="pc-error"></p>
          </td>
          <td><?php echo form_input(array(
              'name' => 'user',
              'value' => '',
              'required' => 'required',
              'placeholder' => 'ユーザー名',
              'class' => 'pc-input'
            )); ?></td>
        </tr>
        <tr>
          <td>
            <h3 class="pc-sub_title">管理者メールアドレス</h3>
            <p class="pc-paragraph">Phage Coreの様々な通知に使われるメールアドレスです</p>
            <p class="pc-error"></p>
          </td>
          <td><?php echo form_input(array(
              'name' => 'mail',
              'value' => '',
              'required' => 'required',
              'placeholder' => 'mail@example.com',
              'class' => 'pc-input'
            )); ?></td>
        </tr>
        <tr>
          <td>
            <h3 class="pc-sub_title">パスワード</h3>
            <p class="pc-paragraph">Phage Coreはパスワードについて1024文字以下という制約以外を排しています。是非パスワードのベストプラクティスを理解し、かつあなたが使用しやすいパスワードを使用してください。</p>
            <p class="pc-error"></p>
          </td>
          <td><?php echo form_password(array(
              'name' => 'password',
              'value' => '',
              'required' => 'required',
              'placeholder' => '',
              'class' => 'pc-input'
            )); ?></td>
        </tr>
        <tr>
          <td>
            <h3 class="pc-sub_title">パスワード（再入力）</h3>
            <p class="pc-paragraph">上記のパスワードを再入力してください。</p>
            <p class="pc-error"></p>
          </td>
          <td><?php echo form_input(array(
              'name' => 'passconf',
              'value' => '',
              'required' => 'required',
              'placeholder' => '',
              'class' => 'pc-input'
            )); ?></td>
        </tr>
        <tr>
          <td>
            <h3 class="pc-sub_title">データベースプレフィックス</h3>
            <p class="pc-paragraph">多くの場合は初期設定で問題ないですが、もしあなたが複数のサイトを運営しようとしているならこの値を変更すべきです。また、入力できる文字は半角アルファベット50文字以下に制限されます。</p>
            <p class="pc-error"></p>
          </td>
          <td><?php echo form_input(array(
              'name' => 'db_prefix',
              'value' => 'pc',
              'required' => 'required',
              'placeholder' => 'pc',
              'class' => 'pc-input'
            )); ?>_</td>
        </tr>
        </tbody>
      </talbe>
      <div class="pc-submit-wrapper">
        <?php echo form_submit(array(
          'name' => 'submit',
          'value' => 'この設定で始める',
          'required' => 'required',
          'placeholder' => '',
          'class' => 'pc-submit pc-noAction'
        )); ?>
      </div>
      <?php echo form_close() ?>
    </section>
  </div>
</div>
<footer>
  <p class="pc-footer"><small class="pc-footer_caption">© 2018 Phage Core, proudly powered by <a href="https://github.com/Go-Noji/PhageCore" target="_blank">Phage Core</a></small></p>
</footer>
<?php foreach ($this->link_files->get_script() as $script) : ?>
  <script src="<?php echo $script; ?>"></script>
<?php endforeach; ?>
</body>
</html>