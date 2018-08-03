<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property Options_model $options_model
 */
class Options_station extends PC_Model
{

  /**
   * Options_station constructor.
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    //modelのロード
    $this->load->model('database/options_model');
  }

  /**
   * 管理画面で扱うことが可能なデータを取得する
   * @return mixed
   */
  public function multiple()
  {
    return array(
      'fields' => array('ID', '設定名', '設定値'),
      'id'=> 'id',
      'link' => 'key_name',
      'data' => $this->options_model->get_controllable_data()
    );
  }

}