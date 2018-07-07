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
<div class="pc-wrapper">
  <main role="main">
    <div class="pc-loginWrapper">
      <h1 class="pc-loginHead">
        <img class="pc-installLogo" src="<?php echo site_url('images/logo.png'); ?>" alt="ロゴ">
        <span><?php echo html_escape($site_name); ?></span>
      </h1>
      <p><?php echo $this->input->get('first') ? 'ようこそ': ''; ?></p>
      <?php if ($ban) : ?>
        <p class="pc-error">現在ログイン制限中です。あと<?php echo $release < 60 ? 1 : floor($release / 60); ?>分お待ちください。</p>
      <?php else : ?>
        <?php echo $limit ? '<p class="pc-error">あと'.$limit.'回でログイン制限されます</p>' : ''; ?>
        <?php echo form_open('', array('id' => 'loginForm', 'class' => 'pc-loginForm')); ?>
        <p><label for="mail">ID</label><br><input name="mail" value="" id="mail" class="login_form_item" type="text"></p>
        <p><label for="password">ID</label><br><input name="password" value="" id="password" class="login_form_item" type="text"></p>
        <input name="submit" value="ログイン" class="login_form_item" type="submit">
        <?php echo form_close(); ?>
      <?php endif; ?>
    </div>
  </main>
  <footer></footer>
</div>
<?php foreach ($this->link_files->get_script() as $script) : ?>
  <script src="<?php echo $script; ?>"></script>
<?php endforeach; ?>
</body>
</html>