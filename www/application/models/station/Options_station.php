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
      'fields' => array(
        array(
          'name' => 'id',
          'label' => 'id'
        ),
        array(
          'name' => 'key_name',
          'label' => '設定名'
        ),
        array(
          'name' => 'value',
          'label' => '設定値'
        )
      ),
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
      'fields' => array(
        'id' => array(
          'label' => 'id',
          'control' => FALSE,
          'type' => 'text',
          'options' => array()
        ),
        'key_name' => array(
          'label' => '設定名',
          'control' => TRUE,
          'type' => 'select',
          'options' => array(
            'http://localhost/PhageCore/images/logo.png' => 'http://localhost/PhageCore/images/logo.png',
            'https://pbs.twimg.com/profile_images/739836658455961602/0bwfa8IM_bigger.jpg' => 'https://pbs.twimg.com/profile_images/739836658455961602/0bwfa8IM_bigger.jpg'
          )
        ),
        'value' => array(
          'label' => '設定値',
          'control' => TRUE,
          'type' => 'text',
          'options' => array()
        )
      ),
      'data' => $this->options_model->get_controllable($id)
    );
  }

}