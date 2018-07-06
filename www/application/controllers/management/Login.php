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
 * @property Login_tool $login_tool
 * @property Install_model $install_model
 * @property Admin_model $admin_model
 */
class Login extends CI_Controller
{

  /**
   * Login constructor.
   */
  public function __construct()
  {
    parent::__construct();

    //セッションをスタートさせる
    $this->load->library('session');

    //設定の読み込み
    $this->load->config('phage_config');

    //URLとFORMヘルパーのロード
    $this->load->helper(array('url', 'form'));

    //Install_modelのロード
    $this->load->model('install_model');

    //インストールの必要があればインストール画面へリダイレクト
    if ($this->install_model->is_need_install())
    {
      redirect(site_url('management/install'));
    }

    //ログインライブラリーのロード
    $this->load->library('login_tool', array('admin', 5, 10));

    //ログインしていたら管理画面へリダイレクト
    if ($this->login_tool->is_login())
    {
      redirect(site_url('admin'));
    }

    //Admin_modelのロード
    $this->load->model('admin_model');
  }

  /**
   * ログイン処理を試みるか判断&ログイン処理をする
   * ログインに成功した時のみTRUEを返す
   * @return bool
   */
  private function _login()
  {
    //そもそもログインする意思がない(初回アクセス時等)場合はFALSEを返す
    if ( ! $this->input->post('submit'))
    {
      return FALSE;
    }

    //adminテーブルから該当する情報の取得を試みる
    $admin = $this->admin_model->get_admin_by_mail_and_password((string)$this->input->post('mail'), (string)$this->input->post('password'));

    //ログイン成功処理
    if (isset($admin['id']))
    {
      $this->login_tool->login($admin['id']);

      return TRUE;
    }


    //ここからは失敗なのでログイン制限回数をインクリメント
    //(この行をコメントアウトすると回数制限をしない)
    $this->login_tool->set_failure();

    //失敗としてFALSEを返す
    return FALSE;
  }

  /**
   * ログイン画面の表示
   */
  public function index()
  {
    //ログインが確定したらリダイレクト先に飛ぶ
    if ($this->_login())
    {
      redirect(site_url('admin'));
    }

    //ログイン制限中フラグ・ログイン制限残り回数・ログイン制限開放時間
    $ban = $this->login_tool->is_limit();
    $limit = ! $ban && $this->input->post('submit') ? $this->login_tool->get_limit() : 0;
    $release = $ban ? $this->login_tool->get_release() : 0;

    //viewを読み込む
    $this->load->view('admin/login', compact('ban', 'limit', 'release'));
  }

}