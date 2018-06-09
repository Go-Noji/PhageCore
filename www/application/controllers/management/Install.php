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
 * @property Link_files $link_files
 */
class Install extends CI_Controller
{

  /**
   * エラーメッセージ
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

    //リンクファイルライブラリのロード
    $this->load->library('link_files');

    //インストールモデルのロード
    $this->load->model('install_model');
  }

  /**
   * バリデーションを行う
   * @return bool
   */
  private function _validation()
  {
    $this->form_validation->set_rules(array(
      array(
        'field' => 'user',
        'label' => '管理者名',
        'rules' => 'required|max_length[256]',
        'errors' => array(
          'required' => '{field}は必須です',
          'max_length' => '{field}は{param}文字以下で入力してください'
        )
      ),
      array(
        'field' => 'mail',
        'label' => '管理者メールアドレス',
        'rules' => 'required|valid_email',
        'errors' => array(
          'required' => '{field}は必須です',
          'valid_email' => '{field}が正しくありません'
        )
      ),
      array(
        'field' => 'password',
        'label' => 'パスワード',
        'rules' => 'required|max_length[1024]',
        'errors' => array(
          'required' => '{field}は必須です',
          'max_length' => '{field}は{param}文字以下で入力してください'
        )
      ),
      array(
        'field' => 'passconf',
        'label' => 'パスワード（再入力）',
        'rules' => 'match[password]',
        'errors' => array(
          'required' => '{field}が一致しません'
        )
      ),
      array(
        'field' => 'db_prefix',
        'label' => 'データベースプレフィックス',
        'rules' => 'required|max_length[50]|alpha',
        'errors' => array(
          'required' => '{field}は必須です',
          'max_length' => '{field}は{param}文字以下で入力してください',
          'alpha' => '{field}は半角アルファベットのみ入力できます'
        )
      )
    ));
    return $this->form_validation->run();
  }

  /**
   * DBをイニシャライズし、データを挿入する
   * エラーが発生した場合はエラーメッセージをerrorへ格納する
   */
  private function _install()
  {
    //DBをイニシャライズする
    if ($this->install_model->install(TRUE))
    {
      $this->error = 'データベースエラーが発生しました';
      return FALSE;
    }

    return TRUE;
  }

  /**
   * JSONを吐き出してスクリプトを終了させる
   * @param int $status
   * @param array $data
   * @return void
   */
  protected function _outPutJson($status = 200, $data = array())
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
   * バリデーションを実行し、通ればデータベースを更新する
   * JSONを出力する
   */
  public function validation()
  {
    $this->_outPutJson($this->_validation() && $this->_install() ? 200 : 400, array(
      'validation' => validation_errors(),
      'error' => $this->error
    ));
  }

  /**
   * インストール画面
   * @return void
   */
  public function index()
  {
    $this->link_files->add_file('dist/bundle.js');
    $this->load->view('admin/install');
  }

}
