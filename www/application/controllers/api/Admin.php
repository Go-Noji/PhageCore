<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Output $output
 * @property CI_Input $input
 */
class Admin extends PH_Controller
{

  /**
   * Admin constructor.
   */
  public function __construct()
  {
    parent::__construct();

    //ログインライブラリーのロード
    $this->load->library('login_tool', array('admin', 5, 10));

    //ログイン済か検証
    //ログインしていなかったら失敗を出力して出力
    if ( ! $this->login_tool->is_login())
    {
      $this->_output_json(400, array('message' => '['.get_class($this).']: 権限が確認できません'));
    }
  }

}
