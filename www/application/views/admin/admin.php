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
<div class="pc-wrapper pc-admin pc-js-fullHeight" style="background-image: <?php echo html_escape($admin_background_image); ?>;">
  <header class="pc-adminHeader pc-js-adminHeader" style="background-color: <?php echo html_escape($theme_color); ?>;">
    <nav class="pc-adminHeaderNav">
      <ul class="pc-adminHeaderNavBox">
        <li class="pc-adminHeaderNavList"><a class="pc-linkReverse" href="<?php echo site_url(); ?>" target="_blank"><?php echo html_escape($site_name); ?></a></li>
      </ul>
      <ul class="pc-adminHeaderNavBox">
        <li class="pc-adminHeaderNavList"><a href="<?php echo site_url('admin/logout'); ?>"><i class="pc-icon pc-iconLink pc-iconReverse fas fa-bell"></i></a></li>
        <li class="pc-adminHeaderNavList"><a href="<?php echo site_url('admin/logout'); ?>"><i class="pc-icon pc-iconLink pc-iconReverse fas fa-envelope"></i></a></li>
        <li class="pc-adminHeaderNavList"><a href="<?php echo site_url(); ?>" target="_blank"><i class="pc-icon pc-iconLink pc-iconReverse fas fa-user"></i></a></li>
      </ul>
    </nav>
    <nav class="pc-adminHeaderTab">
      <ul id="pc-adminHeaderTabBox" class="pc-adminHeaderTabBox"></ul>
    </nav>
  </header>
  <div class="pc-adminBody">
    <nav id="pc-adminSidebar" class="pc-adminSidebar pc-js-adminSidebar">
      <ul class="pc-adminSidebarBox">
        <li class="pc-adminSidebarList">
          <a>
            <i class="pc-icon fas fa-pencil-alt"></i>
            <span class="pc-linkReverse">ページ</span>
          </a>
        </li>
        <li class="pc-adminSidebarList">
          <a>
            <i class="pc-icon fas fa-tags"></i>
            <span class="pc-linkReverse">属性</span>
          </a>
        </li>
        <li class="pc-adminSidebarList">
          <a>
            <i class="pc-icon fas fa-folder"></i>
            <span class="pc-linkReverse">ディレクトリ</span>
          </a>
        </li>
        <li class="pc-adminSidebarList">
          <a>
            <i class="pc-icon fas fa-file"></i>
            <span class="pc-linkReverse">リソース</span>
          </a>
        </li>
        <li class="pc-adminSidebarList">
          <a>
            <i class="pc-icon fas fa-user"></i>
            <span class="pc-linkReverse">管理者</span>
          </a>
        </li>
        <li class="pc-adminSidebarList">
          <a>
            <i class="pc-icon fas fa-users"></i>
            <span class="pc-linkReverse">ユーザー</span>
          </a>
        </li>
        <li class="pc-adminSidebarList">
          <a>
            <i class="pc-icon fas fa-cog"></i>
            <span class="pc-linkReverse">設定</span>
          </a>
        </li>
      </ul>
    </nav>
    <div class="pc-adminArea pc-js-adminArea">
      <div id="pc-adminTrashArea" class="pc-adminTrashArea">削除する</div>
      <main role="main">
        <div id="pc-adminDesktop" class="pc-adminDesktop">
          <div class="pc-adminDesktopBackground"></div>
          <div class="pc-adminDesktopArea"></div>
        </div>
        <div id="pc-adminWindows" class="pc-adminWindows"></div>
      </main>
    </div>
  </div>
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