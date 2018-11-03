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
class PC_Model extends CI_Model
{

  private $error = false;

  /**
   * PC_Model constructor.
   */
  public function __construct()
  {
    parent::__construct();

    $this->load->database();
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