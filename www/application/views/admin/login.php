<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
  <meta name="description" content="Phage Core ログイン">
  <title>Phage Core ログイン</title>
  <link rel="icon" href="images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="images/apple-touch-icon.png" sizes="180x180">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-TileImage" content="/favicon/mstile-144x144.png">
  <meta name="theme-color" content="#007386">
</head>
<body>
<div class="pc-wrapper pc-login">
  <main role="main">
    <div id="pc-loginArea" class="pc-loginArea pc-js-fullHeight">
      <transition name="login">
        <section class="pc-loginBox" v-show="show" style="display: none;">
          <h1 class="pc-loginHead">
            <img class="pc-loginLogo" src="<?php echo html_escape($site_logo); ?>" alt="ロゴ">
            <span><?php echo html_escape($site_name); ?></span>
          </h1>
          <p class="pc-loginMessage"><?php echo $this->input->get('first') ? 'ようこそ': ''; ?></p>
          <?php if ($ban) : ?>
            <p class="pc-error">現在ログイン制限中です。あと<?php echo $release < 60 ? 1 : floor($release / 60); ?>分お待ちください。</p>
          <?php else : ?>
            <?php echo $limit ? '<p class="pc-error">あと'.$limit.'回でログイン制限されます</p>' : ''; ?>
            <?php echo form_open('', array(
              'id' => 'loginForm',
              'class' => 'pc-loginForm',
              '@submit.prevent' => 'submit'
            )); ?>
            <p>
              <label for="mail">ID</label>
              <br>
              <input name="mail" value="" id="mail" class="pc-loginInput pc-input" type="text" v-model="id">
            </p>
            <p>
              <label for="password">パスワード</label>
              <br>
              <input name="password" value="" id="password" class="pc-loginInput pc-input" type="text" v-model="password">
            </p>
            <p class="pc-loginSubmitBox">
              <input name="submit" value="ログイン" class="pc-loginSubmit pc-submit" type="submit">
            </p>
            <?php echo form_close(); ?>
          <?php endif; ?>
        </section>
      </transition>
    </div>
  </main>
  <footer></footer>
</div>
<?php foreach ($this->link_files->get_script() as $script) : ?>
  <script src="<?php echo $script; ?>"></script>
<?php endforeach; ?>
</body>
</html>