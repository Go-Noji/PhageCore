<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Config $config
 * @property CI_Form_validation $form_validation
 * @property Install_model $install_model
 */
class Install extends CI_Controller
{

  /**
   * @var string
   */
  private $error = '';

  /**
   * Install constructor.
   */
  public function __construct()
  {
    parent::__construct();

    //セッションをスタートさせる
    $this->load->library('session');

    //設定の読み込み
    $this->load->config('phage_config');

    //URLとFORM、Loginヘルパーのロード
    $this->load->helper(array('url', 'form', 'login'));

    //バリデーションライブラリのロード
    $this->load->library('form_validation');

    //インストールモデルのロード
    $this->load->model('install_model');
  }

  /**
   * インストール画面
   * @return void
   */
  public function index()
  {
    $this->install_model->install(TRUE);
  }

}