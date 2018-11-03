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
   * このクラスが扱うデータの定義表
   * @var array
   */
  private $FIELDS = array();

  /**
   * Options_station constructor.
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    //データベース・表示の相互変換定義をロード、プロパティに設定
    $this->config->load('phage_data_fields', TRUE);
    $this->FIELDS = $this->config->item('options_data_fields', 'phage_data_fields');

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
      'fields' => $this->FIELDS,
      'id'=> 'id',
      'link' => 'key_name',
      'data' => $this->options_model->get_controllable_data()
    );
  }

  /**
   * 管理画面で扱うことが可能なデータ単体を取得する
   * @return mixed
   */
  public function get($id)
  {
    return array(
      'fields' => $this->FIELDS,
      'data' => $this->options_model->get_controllable($id)
    );
  }

}