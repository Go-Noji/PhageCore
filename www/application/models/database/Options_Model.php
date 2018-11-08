<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Config $config
 * @property CI_DB $db
 */
class Options_model extends CI_Model
{

  /**
   * Options_Model constructor.
   */
  public function __construct()
  {
    parent::__construct();

    //データベースライブラリのロード
    $this->load->database();

    //config/options.phpによって上書きされるデータを取得
    $this->config->load('options', TRUE);
  }

  /**
   * $key_nameで設定された設定がconfig/options.phpによって上書きされているかを判定
   * @param $key_name
   * @return bool
   */
  private function _is_override($key_name)
  {
    return array_key_exists($key_name, $this->config->item('options'));
  }

  /**
   * $key_nameで取得した設定のレコードを返却する
   * もしDBに該当データが無ければ$fallback(デフォルト)をvalueとし、
   * idは0, key_nameは$key_name, controlは0の疑似的なレコードが返却される
   * $fallbackは文字列かNULLを許容する
   * config/options.phpにデータが存在する場合はそちらが優先的に返される
   * この場合もidは0, key_nameは$key_name, controlは0の疑似的なレコードが返却される
   * @param string $key_name
   * @param string|null $fallback
   * @return array
   */
  public function get_row($key_name, $fallback = NULL)
  {
    //config/options.phpに同名データが存在したら即返却
    if ($this->_is_override($key_name))
    {
      return array(
        'id' => '0',
        'key_name' => $key_name,
        'value' => $this->config->item($key_name, 'options'),
        'description' => '',
        'control' => '0'
      );
    }

    //$key_nameと$fallbackの整形
    $key_name = (string)$key_name;
    $fallback = $fallback === NULL ? NULL : (string)$fallback;

    //SQL文の発行
    $query = $this->db->query("
    SELECT *  
    FROM {$this->db->dbprefix('options')} 
    WHERE key_name = ? 
    LIMIT 1
    ", array($key_name));

    //取得
    $result = $query->row_array();

    //結果返却
    return isset($result['value']) ? $result : array(
      'id' => '0',
      'key_name' => $key_name,
      'value' => $fallback,
      'description' => '',
      'control' => '0'
    );
  }

  /**
   * 管理画面でコントロール可能なoptionsデータを単体をid指定して取得する
   * コントロール不能のデータはcontrolフィールドの値が0のデータが該当する
   * ただし、$ignoreControlがTRUEだった場合のみcontrolフィールドが無視される
   * 該当するデータが無い、もしくは
   * config/options.phpにて上書きされている場合は空配列が返る
   * @param int $id
   * @param bool $ignoreControl
   * @return array
   */
  public function get_row_by_id($id, $ignoreControl = FALSE)
  {
    //$ignoreControlがTRUEだった場合はcontrolフィールドを無視する
    $whereSQL = $ignoreControl ? 'AND control = 1' : '';

    //データベースのデータを取得
    $result = $this->db
      ->query("
      SELECT id, key_name, value, description 
      FROM {$this->db->dbprefix('options')} 
      WHERE id = ?  
      {$whereSQL} 
      LIMIT 1
      ", array($id))
      ->row_array();

    //そもそも空、もしくはconfig/options.phpにて上書きされている場合は空配列を返す
    return ! isset($result['key_name']) || $this->_is_override($result['key_name']) ? array() : $result;
  }

  /**
   * $key_nameで指定された設定のvalueのみを返却する
   * 該当データが無かった場合は$fallbackの値がそのまま返る
   * @param $key_name
   * @param string|null $fallback
   * @return string|null
   */
  public function get($key_name, $fallback = NULL)
  {
    $result = $this->get_row($key_name, $fallback);
    return $result['value'];
  }

  /**
   * optionsテーブルとconfig/options.phpに定義されている全ての設定を返す
   * optionsテーブルのデータはconfig/options.phpの同名データで上書きされる
   * @return array
   */
  public function get_data()
  {
    //データベースのデータを取得
    $query = $this->db->query("
    SELECT * 
    FROM {$this->db->dbprefix('options')}
    ");
    $results = (array)$query->result_array();

    //設定ファイルのデータ構造をデータベースのものと合わせて合成
    foreach ($this->config->item('options') as $key_name => $value)
    {
      $results[$key_name] = $value;
    }

    //返す
    return $results;
  }

  /**
   * 管理画面でコントロール可能なoptionsデータを取得する
   * コントロール不能のデータはcontrolフィールドの値が0、
   * もしくはconfig/options.phpにて上書きされている値が該当する
   * ただし、$ignoreControlがTRUEだった場合のみcontrolフィールドは無視される
   * 結果配列にcontrolフィールドの値が含まれない点に注意
   * @param bool $ignoreControl
   * @return array
   */
  public function get_controllable_data($ignoreControl = FALSE)
  {
    //$ignoreControlがTRUEだった場合はcontrolフィールドを無視する
    $whereSQL = $ignoreControl ? 'WHERE control = 1' : '';

    //データベースのデータを取得
    $query = $this->db->query("
    SELECT id, key_name, value, description 
    FROM {$this->db->dbprefix('options')} 
    {$whereSQL}
    ");
    $results = (array)$query->result_array();

    //config/options.phpに存在するデータは取得しない
    foreach ($results as $index => $result)
    {
      if ($this->_is_override($result['key_name']))
      {
        unset($results[$index]);
      }
    }

    //値を返却
    return $results;
  }

  /**
   * $key_nameと$valueでoptionsテーブルへINSERT or UPDATEを行う
   * INSERTかUPDATEかは自動で判別され実行されるが、
   * $overrideをFALSEに設定し、かつ既に$key_nameで指定された設定が存在した場合は
   * DBへの変更が行われず、存在するレコードのIDが返却される
   * 返り値は操作したレコードのIDだが、DB操作に失敗した場合は0が返る
   * config/options.phpの設定値には関係なくデータベースへの操作が行われるが、
   * それはこのメソッドによる変更が必ずしもget系メソッドの結果にならないことを意味する
   * @param $key_name
   * @param $value
   * @param bool $override
   * @return int
   */
  public function set_by_key($key_name, $value, $override = TRUE)
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
   * $idで指定されたデータの
   * @param $id
   * @param $field
   * @param $value
   * @return int
   */
  public function set($id, $field, $value)
  {
    //引数の整形
    $id = (int)$id;
    $field = (string)$field;
    $value = (string)$value;

    //$fieldが存在しないフィールドだった場合は0を返す
    if ( ! in_array($field, array('key_name', 'value', 'description'), TRUE))
    {
      return 0;
    }

    //既に$key_nameで指定された値があるか確認
    $past = $this->db
      ->query("
      SELECT * 
      FROM {$this->db->dbprefix('options')} 
      WHERE id = ? 
      LIMIT 1
      ", array($id))
      ->row_array();

    //データが存在しない場合は0を返す
    if ( ! isset($past['id']))
    {
      return 0;
    }

    //UPDATEを実行し、成功なら変更したレコードのID、失敗なら0を返す
    return $this->db->update($this->db->dbprefix.'options', array(
      $field => $value
    ), array(
      'id' => $past['id']
    )) ? (int)$past['id'] : 0;
  }

  /**
   * $key_nameで指定したデータを物理削除する
   * set()と同じく、config/options.phpの設定値には関係なくデータベースへの操作が行われる
   * つまり、削除してもconfig/options.phpに設定が存在したらget系メソッドで取得できる
   * @param string $key_name
   */
  public function delete($key_name)
  {
    $this->db->delete($this->db->dbprefix.'options', array('key_name' => $key_name));
  }

}
