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

}