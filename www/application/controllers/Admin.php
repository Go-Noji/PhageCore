<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Config $config
 * @property CI_Session $session
 * @property Login_tool $login_tool
 */
class Admin extends CI_Controller
{

  /**
   * Admin constructor.
   */
  public function __construct()
  {
    parent::__construct();

    //セッションをスタートさせる
    $this->load->library('session');

    //設定の読み込み
    $this->load->config('phage_config');

    //ログインライブラリーのロード
    $this->load->library('login_tool', array('admin', 5, 10));

    //URLヘルパーの読み込み
    $this->load->helper(array('url'));

    //ログイン済か検証
    //ログインしていなかったらリダイレクト
    if ( ! $this->login_tool->is_login())
    {
      redirect(site_url('management/login'));
    }
  }

  /**
   * ログアウトする
   * その後、ログイン画面へリダイレクト
   * @return void
   */
  public function logout()
  {
    $this->login_tool->logout();
    redirect(site_url('management/login'));
  }

  /**
   *  管理画面
   * @return void
   */
  public function index()
  {
    $this->load->view('admin/admin');
  }

}