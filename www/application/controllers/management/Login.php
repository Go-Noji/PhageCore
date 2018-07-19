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
 * @property CI_Output $output
 * @property Login_tool $login_tool
 * @property Install_model $install_model
 * @property Admin_model $admin_model
 * @property Options_Model $options_model
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

    //既にログインしていたら管理画面へリダイレクト
    if ($this->login_tool->is_login())
    {
      redirect(site_url('admin'));
    }

    //Admin_model, Options_modelのロード
    $this->load->model('admin_model');
    $this->load->model('options_model');

    //Link_filesライブラリーのロード
    $this->load->library('link_files');
  }

  /**
   * JSONを吐き出してスクリプトを終了させる
   * @param int $status
   * @param array $data
   * @return void
   */
  private function _outPutJson($status = 200, $data = array())
  {
    //ステータスコードを仕込んで出力、スクリプト終了
    if ($status !== 200)
    {
      $this->output->set_status_header($status);
    }
    $this->output->set_content_type('application/json');
    $this->output->set_output(json_encode($data));
    $this->output->_display();
    exit();
  }

  /**
   * ログイン制限中のメッセージを取得する
   * @return string
   */
  private function _getLimitMessage()
  {
    //そもそもログイン制限設定がされていなかったら半角スペースを返す
    if ( ! $this->login_tool->is_limiter() || $this->login_tool->is_no_failure())
    {
      return '';
    }

    //制限中と制限前でエラーエッセージを変化させる
    return $this->login_tool->is_limit()
        ? '現在ログイン制限中です。<br>あと'.$this->login_tool->get_release().'秒お待ち下さい。'
        : 'あと'.$this->login_tool->get_limit().'回でログイン制限されます';
  }

  /**
   * _getLimitMessage()の内容をJSONで返す
   * エラーメッセージはmessageというキーに格納される
   * ステータスコードは必ず200で返る
   */
  public function getLimitMessage()
  {
    $this->_outPutJson(200, array('message' => $this->_getLimitMessage()));
  }

  /**
   * ログイン処理を試みるか判断&ログイン処理をする
   * 成功時はステータスコード200, 失敗時は400でJSONを返す
   * 成功時はadminの各フィールド情報とmessageキーに空文字が入って返却する
   * 失敗時はmessageキーにエラーメッセージが格納されて返却される
   */
  public function login()
  {
    //adminテーブルから該当する情報の取得を試みる
    $admin = $this->admin_model->get_admin_in_login((string)$this->input->post('id'), (string)$this->input->post('password'));

    //ログイン成功処理
    if (isset($admin['id']))
    {
      $this->login_tool->login($admin['id']);

      $this->_outPutJson(200, array_merge($admin, array('message' => '')));
    }


    //ここからは失敗なのでログイン制限回数をインクリメント
    //(この行をコメントアウトすると回数制限をしない)
    $this->login_tool->set_failure();

    //失敗を返す
    $this->_outPutJson(400, array(
      'message' => $this->_getLimitMessage()
    ));
  }

  /**
   * ログイン画面の表示
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

    //viewを読み込む
    $this->link_files->enable_develop_mode();
    $this->link_files->add_file('dist/login.bundle.js');
    $this->load->view('admin/login', compact('site_name', 'site_logo', 'theme_color', 'admin_background_image'));
  }

}