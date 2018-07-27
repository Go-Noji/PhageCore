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
<body class="ph-body">
<div class="ph-wrapper">
  <main role="main">
    <div class="ph-between">
      <section class="ph-logoBack ph-js-full_height">
        <p class="ph-paragraph"><img class="ps-installLogo" src="<?php echo site_url('images/logo.png'); ?>" alt="Phage Core ロゴ"></p>
        <h1>Phage Core<br>インストール</h1>
        <p class="ph-paragraph">Phage Codeのインストールを行います。</p>
        <p class="ph-paragraph">必要情報を入力して「この設定で始める」ボタンを押して下さい。</p>
        <footer>
          <p class="ph-footer"><small class="ph-footerCaption">© 2018 Phage Core, proudly powered by <a class="ph-logoBackLink" href="https://github.com/Go-Noji/PhageCore" target="_blank">Phage Core</a></small></p>
        </footer>
      </section>
      <section class="ph-formWrapper ph-js-fullHeight">
        <h2>必要情報入力</h2>
        <?php echo form_open('', array(
          'id' => 'installForm',
          '@submit.prevent' => 'install'
        )) ?>
        <ul>
          <li>
            <install-input
              ref="site"
              name="site"
              description="サイトの名前です。"
              title="サイト名"
              placeholder="Phage Core"></install-input>
          </li>
          <li>
            <install-input
              ref="name"
              name="name"
              description="Phage Core上で表示される管理者名です。"
              title="管理者名"
              placeholder="Go Noji"></install-input>
          </li>
          <li>
            <install-input
              ref="slug"
              name="slug"
              description="URL等に使われるシステムライクな管理者名です。半角英数字、「_」、「-」が使用できます。"
              title="スラッグ"
              placeholder="go_noji"></install-input>
          </li>
          <li>
            <install-input
              ref="mail"
              name="mail"
              description="Phage Coreの様々な通知に使われるメールアドレスです"
              title="管理者メールアドレス"
              placeholder="mail@example.com"
            ></install-input>
          </li>
          <li>
            <install-input
              ref="password"
              name="password"
              description="Phage Coreはパスワードについて1024文字以下という制約以外を排しています。是非パスワードのベストプラクティスを理解し、かつあなたが使用しやすいパスワードを使用してください。"
              title="パスワード"
              placeholder=""
              type="password"
            ></install-input></li>
          <li><install-input
              ref="passconf"
              name="passconf"
              description="上記のパスワードを再入力してください。"
              title="パスワード（再入力）"
              placeholder=""
              include="password"
              type="password"
            ></install-input>
          </li>
        </ul>
        <div class="ph-submitWrapper">
          <p class="ph-paragraph ph-message ph-paragraphError">{{dbError}}</p>
          <transition name="loader-fade">
            <div class="ph-loaderWrap" v-if="showLoader">
              <div class="ph-loaderBox"></div>
              <p class="ph-loaderMessage">Connecting...</p>
            </div>
            <div v-else>
              <input name="submit" value="この設定で始める" required="required" placeholder="" class="ph-submit ph-noAction" type="submit">
            </div>
          </transition>
        </div>
        <?php echo form_close(); ?>
      </section>
    </div>
  </main>
</div>
<script>
  var csrf_value = '<?php echo $this->security->get_csrf_hash(); ?>';
  var csrf_key = '<?php echo $this->security->get_csrf_token_name(); ?>';
  var site_url = '<?php echo site_url(); ?>';
</script>
<?php foreach ($this->link_files->get_script() as $script) : ?>
  <script src="<?php echo $script; ?>"></script>
<?php endforeach; ?>
</body>
</html>