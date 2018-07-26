<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_DB $db
 */
class Admin_model extends CI_Model
{

  /**
   * Admin_model constructor.
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    //データベースライブラリのロード
    $this->load->database();

    //各種設定のロード
    $this->load->config('phage_config');
  }

  /**
   * メールアドレス or ユーザースラッグとパスワードから管理者データを取得する
   * adminテーブルの該当レコードが配列になって返ってくる
   * 存在しなかった場合は空配列が返る
   * @param string $id
   * @param string $password
   * @return array
   */
  public function get_admin_in_login($id, $password)
  {
    //$idをメールアドレスとして探すSQL文を作成
    $query = $this->db->query("
    SELECT * 
    FROM {$this->db->dbprefix}admin 
    WHERE act = 1 
    AND mail = ? 
    AND password = ? 
    LIMIT 1
    ", array($id, hash('sha256', $password)));

    //取得
    $result = $query->row_array();

    //検索に引っかかったら返却
    if (isset($result['id']))
    {
      return $result;
    }

    //$idをユーザースラッグとして探すSQL文を作成
    $query = $this->db->query("
    SELECT * 
    FROM {$this->db->dbprefix}admin 
    WHERE act = 1 
    AND slug = ? 
    AND password = ? 
    LIMIT 1
    ", array($id, hash('sha256', $password)));

    //取得
    $result = $query->row_array();

    //結果を返却
    return isset($result['id']) && $result['id'] ? $result : array();
  }

  /**
   * $admin_idで指定された管理者が持つ権限で
   * $model, $methodで指定されたModelのメソッドが実行できるか判定する
   * $methodを指定した場合は$model名と同名のModelクラス内にある$methodと同名のメソッドをチェックする
   * この場合、その$model自体が使用不可能な場合もFALSEを返す
   * $methodを省略した場合はその$modelクラス自体が使用可能かどうかをチェックｓる
   * @param int $admin_id
   * @param string $model
   * @param string $method
   * @return bool
   */
  public function is_authority_model($admin_id, $model, $method = '')
  {
    //$methodが存在するかしないかで条件を変える
    $whereSQL = $method === ''
      ? " {$this->db->dbprefix('phage_role_ng_method')} = ".$this->db->escape($model)
      : "({$this->db->dbprefix('phage_role_ng_method')} = ".$this->db->escape($model.'/'.$method)." OR {$this->db->dbprefix('phage_role_ng_method')} = ".$this->db->escape($model).')';

    //SQLの発行
    $query = $this->db->query("
    SELECT {$this->db->dbprefix('phage_role_ng_method')}.id 
    FROM {$this->db->dbprefix('phage_role_ng_method')} 
    LEFT JOIN {$this->db->dbprefix('admin')} 
    ON {$this->db->dbprefix('phage_role_ng_method')}.role_id = {$this->db->dbprefix('admin')}.role_id 
    WHERE {$whereSQL} 
    AND {$this->db->dbprefix('admin')}.id = ? 
    LIMIT 1
    ", array($admin_id));
    $result = $query->row_array();

    //存在したらFALSE, しなかったらTRUEを返す
    return isset($result['id']) ? FALSE : TRUE;
  }

}