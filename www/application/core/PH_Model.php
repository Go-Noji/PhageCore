<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_DB $db
 * @property CI_Config $config
 */
class PH_Model extends CI_Model
{

  /**
   * Model領域で起こったエラーを捕捉するためのbool値
   * @var bool
   */
  protected $error = FALSE;

  /**
   * Stationモデルを呼ぶべきControllerのメソッドタイプを定義する
   * キーにStationメソッド名、値にControllerメソッド名を設定する
   * @var array
   */
  private $method_type = array();

  /**
   * CI_Modelのコンストラクタを呼ぶ
   * PH_Model constructor.
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Stationモデルが持つ、Controllerから呼び出されるパブリックメソッドの定義を登録する
   * この定義に合わないControllerメソッドから呼び出されないとControllerは不正アクセスと判定し、
   * Stationモデルが持つメソッドを実行しない
   * $methodにはStationモデルのメソッド名, $typeにはControllerのメソッド名('call'や'mutation')を設定する
   * @param string $method
   * @param string $type
   */
  protected function _set_method_type($method, $type = 'call')
  {
    $this->method_type[(string)$method] = (string)$type;
  }

  /**
   * $methodで指定されたメソッドが$typeでアクセスされるべきメソッドか判定する
   * @param string $method
   * @param string $type
   * @return bool
   */
  public function is_method_type($method, $type)
  {
    //もし$method, もしくは$typeが文字列でなかった場合はFALSEを返す
    if ( ! is_string($method) || ! is_string($type))
    {
      return FALSE;
    }

    //結果判定
    return isset($this->method_type[$method]) && $this->method_type[$method] === $type;
  }

  /**
   * このモデルでエラーが発生したかを返す
   * 実際は$this->errorを返しているだけ
   * @return bool
   */
  public function is_error()
  {
    return $this->error;
  }

}
