<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 */
class Select extends PC_Controller
{

  /**
   * Select constructor.
   */
  public function __construct()
  {
    parent::__construct();

    //設定の読み込み
    $this->load->config('phage_config');
  }

}