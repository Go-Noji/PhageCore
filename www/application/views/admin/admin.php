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
<div id="ph-admin" class="ph-wrapper ph-admin ph-js-fullHeight" style="background-image: <?php echo html_escape($admin_background_image); ?>;">
  <header class="ph-adminHeader ph-js-adminHeader" style="background-color: <?php echo html_escape($theme_color); ?>;">
    <nav class="ph-adminHeaderNav">
      <ul class="ph-adminHeaderNavBox">
        <li class="ph-adminHeaderNavList"><a class="ph-reverseColor" href="<?php echo site_url(); ?>" target="_blank"><?php echo html_escape($site_name); ?></a></li>
      </ul>
      <ul class="ph-adminHeaderNavBox">
        <li class="ph-adminHeaderNavList"><a href="<?php echo site_url('admin/logout'); ?>"><i class="ph-icon ph-iconLink ph-reverseColor fas fa-bell"></i></a></li>
        <li class="ph-adminHeaderNavList"><a href="<?php echo site_url('admin/logout'); ?>"><i class="ph-icon ph-iconLink ph-reverseColor fas fa-envelope"></i></a></li>
        <li class="ph-adminHeaderNavList"><a href="<?php echo site_url(); ?>" target="_blank"><i class="ph-icon ph-iconLink ph-reverseColor fas fa-user"></i></a></li>
      </ul>
    </nav>
    <nav class="ph-adminHeaderTab">
      <ul id="ph-adminHeaderTabBox" class="ph-adminHeaderTabBox"></ul>
    </nav>
  </header>
  <div class="ph-adminBody">
    <nav id="ph-adminSidebar" class="ph-adminSidebar ph-js-adminSidebar">
      <ul class="ph-adminSidebarBox">
        <sidebar-list
          ref="content"
          title="コンテンツ"
          to="/content"
          icon="fa-pencil-alt"></sidebar-list>
        <sidebar-list
          ref="attribute"
          title="属性"
          to="/attribute"
          icon="fa-tags"></sidebar-list>
        <sidebar-list
          ref="directory"
          title="ディレクトリ"
          to="/directory"
          icon="fa-folder"></sidebar-list>
        <sidebar-list
          ref="resource"
          title="リソース"
          to="/resource"
          icon="fa-file"></sidebar-list>
        <sidebar-list
          ref="admin"
          title="管理者"
          to="/admin"
          icon="fa-user"></sidebar-list>
        <sidebar-list
          ref="user"
          title="ユーザー"
          to="/user"
          icon="fa-users"></sidebar-list>
        <sidebar-list
          ref="options"
          title="設定"
          to="/options"
          icon="fa-cog"></sidebar-list>
      </ul>
    </nav>
    <div class="ph-adminArea ph-js-adminArea">
      <div id="ph-adminTrashArea" class="ph-adminTrashArea">削除する</div>
      <main role="main">
        <div id="ph-adminDesktop" class="ph-adminDesktop">
          <div class="ph-adminDesktopBackground"></div>
          <div class="ph-adminDesktopArea">
            <router-view></router-view>
          </div>
        </div>
        <div id="ph-adminWindows" class="ph-adminWindows"></div>
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