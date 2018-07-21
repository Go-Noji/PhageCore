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
  <meta id="metaDescription" name="description" content="<?php echo html_escape($site_name); ?>">
  <title id="metaTitle"><?php echo html_escape($site_name); ?></title>
  <link rel="icon" href="images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="images/apple-touch-icon.png" sizes="180x180">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-TileImage" content="/favicon/mstile-144x144.png">
  <meta name="theme-color" content="<?php echo $theme_color; ?>">
</head>
<body>
<header class="pc-adminHeader">
  <nav class="pc-adminHeaderNav">
    <ul class="pc-adminHeaderNavBox">
      <li class="pc-adminHeaderNavList"><a href="<?php echo site_url(); ?>" target="_blank">サイトを開く</a></li>
      <li class="pc-adminHeaderNavList"><a href="<?php echo site_url('admin/logout'); ?>">ログアウトする</a></li>
    </ul>
    <ul class="pc-adminHeaderNavBox">
      <li class="pc-adminHeaderNavList"><a href="<?php echo site_url('admin/logout'); ?>"><i class="pc-icon fas fa-bell"></i></a></li>
      <li class="pc-adminHeaderNavList"><a href="<?php echo site_url('admin/logout'); ?>"><i class="pc-icon fas fa-envelope"></i></a></li>
      <li class="pc-adminHeaderNavList"><a href="<?php echo site_url(); ?>" target="_blank"><i class="pc-icon fas fa-user"></i></a></li>
    </ul>
  </nav>
  <nav class="pc-adminHeaderTab">
    <ul id="pc-adminHeaderTabBox" class="pc-adminHeaderTabBox"></ul>
  </nav>
</header>
<div class="pc-wrapper pc-login" style="background-color: <?php echo html_escape($theme_color); ?>;background-image: <?php echo html_escape($admin_background_image); ?>;">
  <nav id="pc-adminSidebar">
    <ul class="pc-adminSidebarBox">
      <li class="pc-adminSidebarList">
        <a>
          <i class="pc-icon fas fa-pen"></i>
          <span>ページ</span>
        </a>
      </li>
      <li class="pc-adminSidebarList">
        <a>
          <i class="pc-icon fas fa-tags"></i>
          <span>属性</span>
        </a>
      </li>
      <li class="pc-adminSidebarList">
        <a>
          <i class="pc-icon fas fa-folder"></i>
          <span>ディレクトリ</span>
        </a>
      </li>
      <li class="pc-adminSidebarList">
        <a>
          <i class="pc-icon fas fa-file"></i>
          <span>リソース</span>
        </a>
      </li>
      <li class="pc-adminSidebarList">
        <a>
          <i class="pc-icon fas fa-user"></i>
          <span>管理者</span>
        </a>
      </li>
      <li class="pc-adminSidebarList">
        <a>
          <i class="pc-icon fas fa-users"></i>
          <span>ユーザー</span>
        </a>
      </li>
      <li class="pc-adminSidebarList">
        <a>
          <i class="pc-icon fas fa-cog"></i>
          <span>設定</span>
        </a>
      </li>
    </ul>
  </nav>
  <div id="pc-adminTrashArea" class="pc-adminTrashArea">削除する</div>
  <main role="main">
    <div class="pc-adminArea pc-js-fullHeight">
      <div id="pc-adminDesktop" class="pc-adminDesktop">
        <div class="pc-adminDesktopBackground"></div>
        <div class="pc-adminDesktopArea"></div>
      </div>
      <div id="pc-adminWindows" class="pc-adminWindows"></div>
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