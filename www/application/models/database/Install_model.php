<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Config $config
 * @property CI_Input $input
 * @property CI_DB $db
 */
class Install_model extends CI_Model
{

  /**
   * テーブル名配列
   * @var array
   */
  private $tables;

  /**
   * 最高権限者のrole名
   * @var string
   */
  private $adminstrator = 'administrator';

  /**
   * Install_model constructor.
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    //データベースライブラリのロード
    $this->load->database();

    //各種設定のロード
    $this->load->config('phage_config');

    //テーブル名配列のセット
    //削除時に外部キー設定に引っかかるので、_createで定義する逆順で記載
    $this->tables = array('desktop_icon', 'desktop', 'task', 'resource', 'options', 'contact_meta', 'contact', 'comment_meta', 'comment', 'user_relationship', 'user', 'attribute_relationship', 'attribute', 'content_meta', 'content', 'directory', 'admin', 'role_ng_method', 'role');
  }

  /**
   * 初期化判定ファイルの削除
   * 初期化判定ファイルが存在するにも拘わらず削除に失敗した場合はFALSEを返す
   * @return bool
   */
  private function _delete_reset_file()
  {
    //ファイルが存在するにも拘わらず削除に失敗したらFALSEを返す
    if (file_exists($this->config->item('install_reset_file')) && ! unlink($this->config->item('install_reset_file')))
    {
      return FALSE;
    }

    //削除成功
    return TRUE;
  }

  /**
   * 既にある標準テーブルを消す
   */
  private function _truncate()
  {
    foreach ($this->tables as $table)
    {
      //DROP文実行
      $results['drop_'.$table] =  $this->db->query('
        DROP TABLE 
        IF EXISTS 
        '.$this->db->dbprefix($table));
    }
  }

  /**
   * テーブルを作成する
   * 外部キー設定があるので参照先テーブルから先に記述
   * db->queryの結果を格納した配列を返す
   * @return array
   */
  private function _create()
  {
    $results['role'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('role')}` 
    ( 
      `id` INT(3) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `name` VARCHAR(255) NOT NULL COMMENT '管理権限名', 
      PRIMARY KEY (`id`)
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = '管理権限種類';
    ");

    $results['role_ng_method'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('role_ng_method')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `role_id` INT(3) NOT NULL COMMENT '管理権限ID' , 
      `ng_definition` VARCHAR(255) NOT NULL COMMENT '実行を禁止するModel名/メソッド名', 
      PRIMARY KEY (`id`), 
      CONSTRAINT `role_ng_method_id` FOREIGN KEY (`role_id`) REFERENCES `{$this->db->dbprefix('role')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = '管理権限ごとに禁止するメソッド';
    ");

    $results['admin'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('admin')}` 
    ( 
      `id` INT(6) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `role_id` INT(3) NOT NULL COMMENT '権限ID' , 
      `slug` VARCHAR(50) NOT NULL COMMENT 'スラッグ' , 
      `name` VARCHAR(255) NOT NULL COMMENT '名前' , 
      `mail` VARCHAR(100) NOT NULL COMMENT 'メールアドレス' , 
      `password` VARCHAR(64) NOT NULL COMMENT 'パスワード（sha256）' , 
      `act` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '有効/無効' , 
      PRIMARY KEY (`id`), 
      UNIQUE (`slug`), 
      CONSTRAINT `admin_role_id` FOREIGN KEY (`role_id`) REFERENCES `{$this->db->dbprefix('role')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = '管理者のテーブル';
    ");

    $results['directory'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('directory')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `slug` VARCHAR(50) NOT NULL COMMENT 'スラッグ' , 
      `name` VARCHAR(255) NOT NULL COMMENT '名前' , 
      `order_number` INT(9) NOT NULL COMMENT '順序' , 
      `color` VARCHAR(20) NULL DEFAULT NULL COMMENT '色' , 
      `queue` INT(9) NOT NULL COMMENT 'tree構造のX軸' , 
      `depth` INT(9) NOT NULL COMMENT 'tree構造のY軸' , 
      PRIMARY KEY (`id`), 
      UNIQUE `slug` (`slug`),
      INDEX `queue` (`queue`),
      INDEX `depth` (`depth`,`queue`)
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = '疑似ディレクトリのDB';
    ");

    $results['content'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('content')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `status` VARCHAR(50) NOT NULL COMMENT 'ステータス' , 
      `directory_id` INT(9) DEFAULT NULL COMMENT 'directoryのid' , 
      `admin_id` INT(9) DEFAULT NULL COMMENT 'adminのid' , 
      `slug` VARCHAR(50) NOT NULL COMMENT 'ユニーク投稿スラッグ' , 
      `url` VARCHAR(255) NOT NULL COMMENT 'URL' , 
      `type` VARCHAR(50) NOT NULL COMMENT '種類' , 
      `mime_type` VARCHAR(50) NOT NULL COMMENT 'MIME type' , 
      `order_number` INT(9) NOT NULL COMMENT '順序' , 
      `create_datetime` DATETIME NOT NULL COMMENT '作成日時' , 
      `modify_datetime` DATETIME NOT NULL COMMENT '更新日時' , 
      `queue` INT(9) NOT NULL COMMENT 'tree構造のX軸' , 
      `depth` INT(9) NOT NULL COMMENT 'tree構造のY軸' , 
      `search` MEDIUMTEXT NOT NULL COMMENT '検索用文字列' , 
      PRIMARY KEY (`id`), 
      UNIQUE `slug` (`slug`), 
      INDEX `queue` (`queue`),
      INDEX `depth` (`depth`,`queue`),
      FULLTEXT (`search`), 
      CONSTRAINT `content_directory_id` FOREIGN KEY (`directory_id`) REFERENCES `{$this->db->dbprefix('directory')}` (`id`), 
      CONSTRAINT `content_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `{$this->db->dbprefix('admin')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = '投稿';
    ");

    $results['content_meta'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('content_meta')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `content_id` INT(9) NOT NULL COMMENT 'contentのid' , 
      `key_name` INT(50) NOT NULL COMMENT 'キー' , 
      `value` TEXT NOT NULL COMMENT '値' , 
      PRIMARY KEY (`id`), 
      INDEX `key_name` (`key_name`), 
      CONSTRAINT `content_meta_id` FOREIGN KEY (`content_id`) REFERENCES `{$this->db->dbprefix('content')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'contentに対しての実データ';
    ");

    $results['attribute'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('attribute')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `type_index` INT(2) NOT NULL COMMENT '種類' , 
      `slug` VARCHAR(50) NOT NULL COMMENT 'スラッグ' , 
      `name` VARCHAR(255) NOT NULL COMMENT '名前' , 
      `content` TEXT NOT NULL COMMENT '内容' , 
      `thumbnail` VARCHAR(255) NOT NULL COMMENT 'サムネイルのパス（相対）' , 
      `order_number` INT(9) NOT NULL DEFAULT '0' COMMENT '順番' , 
      `queue` INT(9) NOT NULL COMMENT 'tree構造のX軸' , 
      `depth` INT(9) NOT NULL COMMENT 'tree構造のY軸', 
      PRIMARY KEY (`id`), 
      INDEX `type_index` (`type_index`), 
      INDEX `queue` (`queue`),
      INDEX `depth` (`depth`,`queue`),
      UNIQUE (`slug`)
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'カテゴリーやタグのためのテーブル';
    ");

    $results['attribute_relationship'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
     `{$this->db->dbprefix('attribute_relationship')}` 
     (
      `attribute_id` INT(9) NOT NULL COMMENT 'attributeのID' , 
     `content_id` INT(9) NOT NULL COMMENT 'contentのID' , 
     PRIMARY KEY (`attribute_id`, `content_id`), 
     INDEX `content_id` (`content_id`), 
      CONSTRAINT `attribute_id_relationship` FOREIGN KEY (`attribute_id`) REFERENCES `{$this->db->dbprefix('attribute')}` (`id`), 
      CONSTRAINT `attribute_content_id_relationship` FOREIGN KEY (`content_id`) REFERENCES `{$this->db->dbprefix('content')}` (`id`)
   ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'attributeテーブルとcontentテーブルの中間テーブル';
   ");

    $results['user'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('user')}` 
    ( 
      `id` INT(6) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `name` BLOB NOT NULL COMMENT '名前' , 
      `mail` BLOB NOT NULL COMMENT 'メールアドレス' , 
      `password` VARCHAR(64) NOT NULL COMMENT 'パスワード（sha256）' , 
      `act` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '有効/無効' , 
      PRIMARY KEY (`id`)
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'ユーザーのテーブル';
    ");

    $results['user_relationship'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
     `{$this->db->dbprefix('user_relationship')}` 
     ( `user_id` INT(9) NOT NULL COMMENT 'userのID' , 
     `content_id` INT(9) NOT NULL COMMENT 'contentのID' , 
     `type_index` INT(2) NOT NULL COMMENT '関連種類' , 
     PRIMARY KEY (`user_id`, `content_id`), 
     INDEX `content_id` (`content_id`), 
     INDEX `type_index` (`type_index`), 
      CONSTRAINT `user_id_relationship` FOREIGN KEY (`user_id`) REFERENCES `{$this->db->dbprefix('user')}` (`id`), 
      CONSTRAINT `user_content_id_relationship` FOREIGN KEY (`content_id`) REFERENCES `{$this->db->dbprefix('content')}` (`id`)
   ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'userテーブルとcontentテーブルの中間テーブル';
   ");

    $results['comment'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('comment')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `content_id` INT(9) NOT NULL COMMENT 'contentのid' , 
      `user_id` INT(9) COMMENT 'userのid' , 
      `ip` VARCHAR(40) NOT NULL COMMENT 'IPアドレス' , 
      `name` INT(255) NOT NULL COMMENT '名前' , 
      `queue` INT(9) NOT NULL COMMENT 'tree構造のX軸' , 
      `depth` INT(9) NOT NULL COMMENT 'tree構造のY軸' , 
      `act` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '有効/無効' , 
      PRIMARY KEY (`id`), 
      INDEX `queue` (`queue`),
      INDEX `depth` (`depth`,`queue`),
      CONSTRAINT `comment_content_id` FOREIGN KEY (`content_id`) REFERENCES `{$this->db->dbprefix('content')}` (`id`), 
      CONSTRAINT `comment_user_id` FOREIGN KEY (`user_id`) REFERENCES `{$this->db->dbprefix('user')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'コメント用DB';
    ");

    $results['comment_meta'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('comment_meta')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `comment_id` INT(9) NOT NULL COMMENT 'commentのid' , 
      `key_name` INT(50) NOT NULL COMMENT 'キー' , 
      `value` TEXT NOT NULL COMMENT '値' , 
      PRIMARY KEY (`id`), 
      INDEX `key_name` (`key_name`), 
      CONSTRAINT `comment_meta_id` FOREIGN KEY (`comment_id`) REFERENCES `{$this->db->dbprefix('comment')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'commentに対しての実データ';
    ");

    $results['contact'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('contact')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `user_id` INT(9) COMMENT 'userのid' , 
      `ip` VARCHAR(40) NOT NULL COMMENT 'IPアドレス' , 
      PRIMARY KEY (`id`), 
      CONSTRAINT `contact_user_id` FOREIGN KEY (`user_id`) REFERENCES `{$this->db->dbprefix('user')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'コメント用DB';
    ");

    $results['contact_meta'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('contact_meta')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `contact_id` INT(9) NOT NULL COMMENT 'contactのid' , 
      `key_name` INT(50) NOT NULL COMMENT 'キー' , 
      `value` TEXT NOT NULL COMMENT '値' , 
      PRIMARY KEY (`id`), 
      INDEX `key_name` (`key_name`), 
      CONSTRAINT `contact_meta_id` FOREIGN KEY (`contact_id`) REFERENCES `{$this->db->dbprefix('contact')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'contactに対しての実データ';
    ");

    $results['options'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('options')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `key_name` VARCHAR(50) NOT NULL COMMENT 'キー' , 
      `value` TEXT NOT NULL COMMENT '値' , 
      `control` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '管理画面で制御可能にするかどうか' , 
      PRIMARY KEY (`id`), 
      UNIQUE (`key_name`)
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = '汎用設定データ';
    ");

    $results['resource'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('resource')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `directory_id` INT(9) DEFAULT NULL COMMENT 'directoryのid' , 
      `admin_id` INT(9) DEFAULT NULL COMMENT 'adminのid' , 
      `path` VARCHAR(255) NOT NULL COMMENT 'ファイルパス（相対）' , 
      `mime_type` VARCHAR(100) NOT NULL COMMENT 'image/jpeg等' , 
      `byte_size` INT(9) NOT NULL COMMENT 'ファイルサイズ（バイト）' , 
      `name` VARCHAR(255) NOT NULL COMMENT 'ファイル名' , 
      `remarks` TEXT NOT NULL COMMENT 'ファイル備考' , 
      `order_number` INT(9) NOT NULL DEFAULT 0 COMMENT '順序' , 
      `create_datetime` DATETIME NOT NULL COMMENT '作成日' , 
      `modify_datetime` DATETIME NOT NULL COMMENT '更新日' , 
      `act` BOOLEAN NOT NULL DEFAULT TRUE COMMENT '有効か無効か' , 
      PRIMARY KEY (`id`), 
      CONSTRAINT `resource_directory_id` FOREIGN KEY (`directory_id`) REFERENCES `{$this->db->dbprefix('directory')}` (`id`), 
      CONSTRAINT `resource_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `{$this->db->dbprefix('admin')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'メディアファイル用DB';
    ");

    $results['task'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('task')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `model` VARCHAR(50) NOT NULL COMMENT '実行したいモデル名' , 
      `method` VARCHAR(50) NOT NULL COMMENT '実行したいメソッド名' , 
      `param` TEXT NOT NULL COMMENT 'メソッドのパラメータ' , 
      `start_datetime` DATETIME NOT NULL COMMENT 'この時間を越えたらタスクが実行されるフラグが立つ' , 
      `end_datetime` DATETIME NOT NULL COMMENT 'この時間を超えるとタスクが消される' , 
      `weight` INT(4) NOT NULL DEFAULT '0' COMMENT 'タスクの実行コスト' , 
      PRIMARY KEY (`id`), 
      INDEX `start_datetime` (`start_datetime`), 
      INDEX `end_datetime` (`end_datetime`)
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = 'cron等で叩かれるタスクのためのDB';
    ");

    $results['desktop'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('desktop')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `order_number` INT(9) NOT NULL COMMENT '順序' , 
      PRIMARY KEY (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = '管理画面のデスクトップ';
    ");

    $results['desktop_icon'] = $this->db->query("
    CREATE TABLE IF NOT EXISTS 
    `{$this->db->dbprefix('desktop_icon')}` 
    ( 
      `id` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'AI' , 
      `desktop_id` INT(9) NOT NULL COMMENT 'デスクトップID' , 
      `target_id` INT(9) NOT NULL COMMENT '対象アイテムのID' , 
      `type` VARCHAR (30) NOT NULL COMMENT '対象アイテムのテーブル名' , 
      `image` VARCHAR (255) NOT NULL COMMENT 'アイコン画像' , 
      `order_number` INT(3) NOT NULL COMMENT '順序' , 
      PRIMARY KEY (`id`), 
      CONSTRAINT `desktop_icon_id` FOREIGN KEY (`desktop_id`) REFERENCES `{$this->db->dbprefix('desktop')}` (`id`) 
    ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT = '管理画面のデスクトップ';
    ");

    return $results;
  }

  /**
   * Phage Coreが初期インストールするテーブルが全て揃っているか判定する
   * @return bool
   */
  private function _is_tables_exists()
  {
    foreach ($this->tables as $table)
    {
      if ( ! $this->db->table_exists($this->db->dbprefix($table)))
      {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * 権限データ・管理者・サイト名を初期化する
   * DBエラーが発生したらFALSEを返す
   * @return bool
   */
  private function _initAdmin()
  {
    //テーブルが存在しなかったらエラーを返す
    if ( ! $this->_is_tables_exists())
    {
      return FALSE;
    }

    //テーブルに情報をINSERT
    if ( ! $this->db->insert($this->db->dbprefix('role'), array(
      'name' => 'administrator'
    )))
    {
      return FALSE;
    }
    if ( ! $this->db->insert($this->db->dbprefix('admin'), array(
      'role_id' => 1,
      'slug' => $this->input->post('slug'),
      'name' => $this->input->post('name'),
      'mail' => $this->input->post('mail'),
      'password' => hash('sha256', $this->input->post('password'))
    )))
    {
      return FALSE;
    }
    if ( ! $this->db->insert($this->db->dbprefix('options'), array(
      'key_name' => 'site_name',
      'value' => $this->input->post('site')
    )))
    {
      return FALSE;
    }
    if ( ! $this->db->insert($this->db->dbprefix('options'), array(
      'key_name' => 'site_logo',
      'value' => site_url('images/logo.png')
    )))
    {
      return FALSE;
    }
    if ( ! $this->db->insert($this->db->dbprefix('options'), array(
      'key_name' => 'init_timestamp',
      'value' => time()
    )))
    {
      return FALSE;
    }

    //全て正常に終了
    return TRUE;
  }

  /**
   * 初期テーブルのうち、現在存在しているテーブル名の配列を返す
   * 一つもテーブルが存在しない場合は空配列が返る
   * @return array
   */
  public function get_exists_table()
  {
    //returnする配列
    $results = array();

    //初期テーブルの存在をそれぞれ判断
    //存在していたらテーブル名を$resultsへ格納
    foreach ($this->tables as $table)
    {
      if ($this->db->table_exists($this->db->dbprefix($table)))
      {
        $results[] = $this->db->dbprefix.$table;
      }
    }

    //データを返す
    return $results;
  }

  /**
   * 初期化が必要かどうかを判断する
   * 以下の条件のうちどれかに該当した場合にTRUEを返す
   * トップディレクトリに$this->config->item('install_reset_file')というファイルが存在する
   * PhageCore初期テーブルがなにかしら足らない
   * サイト名が未設定
   * 最高権限の管理者データが一つもない
   * @return bool
   */
  public function is_need_install()
  {
    //初期化ファイルがあるかどうかの判断
    if (file_exists($this->config->item('install_reset_file')))
    {
      return TRUE;
    }

    //必要なテーブルが揃っているか検査
    if ( ! $this->_is_tables_exists())
    {
      return TRUE;
    }

    //DBに最高権限の管理者がいるかどうか
    $query = $this->db->query("
    SELECT {$this->db->dbprefix('admin')}.id 
    FROM {$this->db->dbprefix('admin')} 
    LEFT JOIN {$this->db->dbprefix('role')} 
    ON {$this->db->dbprefix('admin')}.role_id = {$this->db->dbprefix('role')}.id 
    WHERE {$this->db->dbprefix('role')}.name = ? 
    LIMIT 1
    ", array($this->adminstrator));
    return $query->row() ? FALSE : TRUE;
  }

  /**
   * 実際にデータベースをインストールする
   * $truncateをTRUEに設定すると既に存在するテーブルを初期化する
   * @param bool $truncate
   * @return bool
   */
  public function install($truncate = FALSE)
  {
    //初期化ファイルの削除
    if ( ! $this->_delete_reset_file())
    {
      return FALSE;
    }

    //既にあるテーブルを消す
    if ($truncate)
    {
      $this->_truncate();
    }

    //CREATE文実行
    //同時にSQLが成功したか判断する結果の配列を取得する
    $results = $this->_create();

    //エラーがあったらFALSEを返す
    foreach ($results as $result) {
      if ( ! $result)
      {
        return FALSE;
      }
    }

    //POSTで送られてきたデータを格納する
    return $this->_initAdmin();
  }

}
