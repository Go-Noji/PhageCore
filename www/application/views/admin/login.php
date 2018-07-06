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
  <div class="login_box">
    <h1 class="login_logo">PhageCore</h1>
    <div>
      <?php if ($ban) : ?>
        <div>
          <p>現在ログイン制限中です。あと<?php
            $minutes = floor($release / 60);
            echo $minutes > 0 ? $minutes : 1;
            ?>分お待ちください。</p>
        </div>
      <?php else : ?>
      <?php
        echo $limit ? '<p>あと'.$limit.'回でログイン制限されます</p>' : '';
        echo form_open('', array('class' => 'login_form'));
        echo '<p><label for="admin_id">ID</label><br>'.form_input(array(
            'id' => 'mail',
            'name' => 'mail',
            'class' => 'login_form_item',
            'value' => set_value('mail')
          )).'</p>';
        echo '<p><label for="admin_password">パスワード</label><br>'.form_password(array(
            'id' => 'password',
            'name' => 'password',
            'class' => 'login_form_item',
            'value' => set_value('password')
          )).'</p>';
        echo form_submit(array(
          'name' => 'submit',
          'class' => 'login_form_item',
          'value' => 'ログイン'
        ));
        echo form_close();
        ?>
      <?php endif; ?>
    </div>
  </div>
  </body>
</html>