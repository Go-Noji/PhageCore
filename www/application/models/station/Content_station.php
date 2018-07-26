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
    return $this->content_model->multiple();
  }

}