<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_DB
 * @property CI_DB $db
 */
class Options_Model extends CI_Model
{

  /**
   * Options_Model constructor.
   */
  public function __construct()
  {
    parent::__construct();

    //データベースライブラリのロード
    $this->load->database();
  }

  /**
   * $key_nameで取得した設定のレコードを返却する
   * もしDBに該当データが無ければ$fallback(デフォルト)をvalueとし、
   * idは0, key_nameは$key_nameの疑似的なレコードが返却される
   * $fallbackは文字列かNULLを許容する
   * @param string $key_name
   * @param string|null $fallback
   * @return array
   */
  private function _get_option($key_name, $fallback)
  {
    //$key_nameと$fallbackの整形
    $key_name = (string)$key_name;
    $fallback = $fallback === NULL ? NULL : (string)$fallback;

    //SQL文の発行
    $query = $this->db->query("
    SELECT *  
    FROM {$this->db->dbprefix}options 
    WHERE key_name = ? 
    LIMIT 1
    ", array($key_name));

    //取得
    $result = $query->row_array();

    //結果返却
    return isset($result['value']) ? $result : array(
      'id' => '0',
      'key_name' => $key_name,
      'value' => $fallback
    );
  }

  /**
   * $key_nameで指定された設定のvalueのみを返却する
   * @param $key_name
   * @param null $fallback
   * @return string|null
   */
  public function get($key_name, $fallback = NULL)
  {
    $result = $this->_get_option($key_name, $fallback);
    return $result['value'];
  }

  /**
   * _get_option()をそのまま呼び出す
   * つまり、optionsテーブルの単一レコードを呼び出す
   * @param $key_name
   * @param null $fallback
   * @return array
   */
  public function get_row($key_name, $fallback = NULL)
  {
    return $this->_get_option($key_name, $fallback);
  }

  /**
   * $key_nameと$valueでoptionsテーブルへINSERT or UPDATEを行う
   * INSERTかUPDATEかは自動で判別され実行されるが、
   * $overrideをFALSEに設定し、かつ既に$key_nameで指定された設定が存在した場合は
   * DBへの変更が行われず、存在するレコードのIDが返却される
   * 返り値は操作したレコードのIDだが、DB操作に失敗した場合は0が返る
   * @param $key_name
   * @param $value
   * @param bool $override
   * @return int
   */
  public function set($key_name, $value, $override = TRUE)
  {
    //引数の整形
    $key_name = (string)$key_name;
    $value = (string)$value;
    $override = (bool)$override;

    //既に$key_nameで指定された値があるか確認
    $past = $this->_get_option($key_name, NULL);

    //UPDATE
    if ($past['id'] !== '0')
    {
      //$overrideがFALSEかつ過去データが存在した場合はDBへ変更を加えない
      if ( ! $override)
      {
        return (int)$past['id'];
      }

      //UPDATEを実行し、成功なら変更したレコードのID、失敗なら0を返す
      return $this->db->update($this->db->dbprefix.'options', array(
        'value' => $value
      ), array(
        'id' => $past['id']
      )) ? (int)$past['id'] : 0;
    }

    //INSERT
    //成功なら挿入したレコードのID、失敗なら0を返す
    return $this->db->insert($this->db->dbprefix.'options', array(
      'key_name' => $key_name,
      'value' => $value
    )) ? $this->db->insert_id() : 0;
  }

  /**
   * $key_nameで指定したデータを物理削除する
   * @param string $key_name
   */
  public function delete($key_name)
  {
    $this->db->delete($this->db->dbprefix.'options', array('key_name' => $key_name));
  }

}