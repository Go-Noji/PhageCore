<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
  <meta name="description" content="<?php echo html_escape($site_name); ?> ログイン">
  <title><?php echo html_escape($site_name); ?>ログイン</title>
  <link rel="icon" href="images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="images/apple-touch-icon.png" sizes="180x180">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-TileImage" content="/favicon/mstile-144x144.png">
  <meta name="theme-color" content="#007386">
</head>
<body>
<div class="ph-wrapper ph-login" style="background-color: <?php echo html_escape($theme_color); ?>;background-image: <?php echo html_escape($admin_background_image); ?>;">
  <main role="main">
    <div id="ph-loginArea" class="ph-loginArea ph-js-fullHeight">
      <transition name="login">
        <section class="ph-loginBox" v-show="show" style="display: none;">
          <h1 class="ph-loginHead">
            <a href="<?php echo site_url(); ?>">
              <img class="ph-loginLogo" src="<?php echo html_escape($site_logo); ?>" alt="ロゴ">
            </a>
            <span><?php echo html_escape($site_name); ?></span>
          </h1>
          <p class="ph-loginMessage"><?php echo $this->input->get('first') ? 'ようこそ': ''; ?></p>
          <?php echo form_open('', array(
            'id' => 'loginForm',
            'class' => 'ph-loginForm',
            '@submit.prevent' => 'submit'
          )); ?>
          <p>
            <label for="id">メールアドレス もしくはユーザースラッグ</label>
            <br>
            <input name="id" value="" id="id" class="ph-loginInput ph-input" type="text" v-model="id">
          </p>
          <p>
            <label for="password">パスワード</label>
            <br>
            <input name="password" value="" id="password" class="ph-loginInput ph-input" type="password" v-model="password">
          </p>
          <transition name="loader-fade">
            <div class="ph-loaderWrap" v-if="showLoader">
              <div class="ph-loaderBox"></div>
              <p class="ph-loaderMessage">Connecting...</p>
            </div>
            <div class="ph-loginSubmitBox" v-else>
              <p class="ph-paragraphError" v-html="error">&nbsp;</p>
              <input name="submit" value="ログイン" class="ph-loginSubmit ph-submit" type="submit">
            </div>
          </transition>
          <?php echo form_close(); ?>
        </section>
      </transition>
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