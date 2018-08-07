<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property Content_model $content_model
 */
class Content_station extends PC_Model
{

  /**
   * Content_model constructor.
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    //modelのロード
    $this->load->model('database/content_model');
  }

  /**
   * contentの情報を集める
   * @return mixed
   */
  public function multiple()
  {
    return array(
      'fields' => array('id', 'name', 'content'),
      'id'=> 'id',
      'link' => 'name',
      'data' => array(
        array(
          'id' => 1,
          'name' => 'サンプル1',
          'content' => '<h1>サンプル1</h1><p>やっほ</p>'
        ),
        array(
          'id' => 2,
          'name' => 'サンプル2',
          'content' => '<h1>サンプル2</h1><p>やっほ</p>'
        ),
        array(
          'id' => 3,
          'name' => 'サンプル3',
          'content' => '<h1>サンプル3</h1><p>やっほ</p>'
        )
      )
    );
  }

}