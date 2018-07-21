<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Config $config
 * @property CI_Session $session
 * @property CI_Input $input
 * @property CI_Form_validation $form_validation
 * @property CI_Output $output
 * @property Login_tool $login_tool
 * @property Admin_model $admin_model
 * @property Options_Model $options_model
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

    //各Modelの読み込み
    $this->load->model('admin_model');
    $this->load->model('options_model');

    //Link_filesライブラリーのロード
    $this->load->library('link_files');
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
    //サイト名の取得
    $site_name = $this->options_model->get('site_name', 'Phage Core');

    //サイトロゴの取得
    $site_logo = $this->options_model->get('site_logo', site_url('images/logo.png'));

    //テーマカラーの取得
    $theme_color = $this->options_model->get('theme_color', '#0099a2');

    //背景画像の取得
    $admin_background_image = $this->options_model->get('admin_background_image', '');
    $admin_background_image = $admin_background_image ? "url('".$admin_background_image."')" : 'none';

    //リソースをロードしつつ画面を表示
    $this->link_files->enable_develop_mode();
    $this->link_files->add_file('dist/admin.bundle.js');
    $this->load->view('admin/admin', compact('site_name', 'site_logo', 'theme_color', 'admin_background_image'));
  }

}