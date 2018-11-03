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
 * @property Login_tool $login_tool
 */
class Install extends CI_Controller
{

  /**
   * エラーメッセージ
   * @var string
   */
  private $error = '';

  /**
   * 管理者名用バリデーション設定
   * @var array
   */
  private $validation_name;

  /**
   * サイト名用バリデーション設定
   * @var array
   */
  private $validation_site;

  /**
   * スラッグ用バリデーション設定
   * @var array
   */
  private $validation_slug;

  /**
   * メールアドレス用バリデーション設定
   * @var array
   */
  private $validation_mail;

  /**
   * パスワード用バリデーション設定
   * @var array
   */
  private $validation_password;

  /**
   * パスワード（再入力）用バリデーション設定
   * @var array
   */
  private $validation_passconf;

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

    //URLとFORMヘルパーのロード
    $this->load->helper(array('url', 'form'));

    //バリデーションライブラリのロード
    $this->load->library('form_validation');

    //リンクファイルライブラリのロード
    $this->load->library('link_files');

    //ログインライブラリーのロード
    $this->load->library('login_tool', array('admin', 5, 10));

    //インストールモデルのロード
    $this->load->model('database/install_model');

    //バリデーション設定
    $this->validation_site = array(
      'field' => 'site',
      'label' => 'サイト名',
      'rules' => 'required|max_length[256]',
      'errors' => array(
        'required' => '{field}は必須です',
        'max_length' => '{field}が長すぎます'
      )
    );
    $this->validation_name = array(
      'field' => 'name',
      'label' => '管理者名',
      'rules' => 'required|max_length[256]',
      'errors' => array(
        'required' => '{field}は必須です',
        'max_length' => '{field}が長すぎます'
      )
    );
    $this->validation_slug = array(
      'field' => 'slug',
      'label' => 'スラッグ',
      'rules' => 'required|max_length[256]|alpha_dash',
      'errors' => array(
        'required' => '{field}は必須です',
        'max_length' => '{field}が長すぎます',
        'alpha_dash' => '{field}に半角英数字、_、-、以外が含まれています'
      )
    );
    $this->validation_mail = array(
      'field' => 'mail',
      'label' => '管理者メールアドレス',
      'rules' => 'required|valid_email',
      'errors' => array(
        'required' => '{field}は必須です',
        'valid_email' => '{field}が正しくありません'
      )
    );
    $this->validation_password = array(
      'field' => 'password',
      'label' => 'パスワード',
      'rules' => 'required|max_length[1024]',
      'errors' => array(
        'required' => '{field}は必須です',
        'max_length' => '{field}は{param}文字以下で入力してください'
      )
    );
    $this->validation_passconf = array(
      'field' => 'passconf',
      'label' => 'パスワード（再入力）',
      'rules' => 'required|matches[password]',
      'errors' => array(
        'required' => '{field}は必須です',
        'matches' => '{field}が一致しません'
      )
    );
  }

  /**
   * バリデーションを行う
   * $targetが'all'の場合、定義されているバリデーション全てが実行される
   * $targetが指定されていた場合、そのfieldに対するバリデーションのみが行われる
   * @param string $target
   * @return bool
   */
  private function _validation($target = 'all')
  {
    //バリデーションの内容を決める
    switch ($target)
    {
      case 'site':
        $this->form_validation->set_rules(array($this->validation_site));
        return $this->form_validation->run();
        break;
      case 'name':
        $this->form_validation->set_rules(array($this->validation_name));
        return $this->form_validation->run();
        break;
      case 'slug':
        $this->form_validation->set_rules(array($this->validation_slug));
        return $this->form_validation->run();
        break;
      case 'mail':
        $this->form_validation->set_rules(array($this->validation_mail));
        return $this->form_validation->run();
        break;
      case 'password':
        $this->form_validation->set_rules(array($this->validation_password));
        return $this->form_validation->run();
        break;
      case 'passconf':
        $this->form_validation->set_rules(array($this->validation_passconf, $this->validation_password));
        return $this->form_validation->run();
        break;
      default:
        $this->form_validation->set_rules(array($this->validation_site, $this->validation_name, $this->validation_slug, $this->validation_mail, $this->validation_password, $this->validation_passconf));
        return $this->form_validation->run();
        break;
    }

    //実行
    return $this->form_validation->run();
  }

  /**
   * DBをイニシャライズし、データを挿入する
   * エラーが発生した場合はエラーメッセージをerrorへ格納する
   */
  private function _install()
  {
    //DBをイニシャライズする
    if ( ! $this->install_model->install(TRUE))
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
   * バリデーションを行う
   * JSONを出力する
   * $targetが'all'の場合、定義されているバリデーション全てが実行され、通過した際はDBが更新される
   * $targetが指定されていた場合、そのfieldに対するバリデーションのみが行われる
   * @param string $target
   */
  public function validation($target = 'all')
  {
    //バリデーション実行
    $result = $this->_validation($target);

    //エラーメッセージ配列の作成
    $errors = array();
    foreach ($target === 'all' ? array('site', 'name', 'slug', 'mail', 'password', 'passconf') : array($target) as $field)
    {
      $errors[$field] = form_error($field, NULL, NULL);
    }

    //この時点で$resultがTRUEかつ、$targetが'all'だったら_install()を実行し結果を上書きする
    if ($result)
    {
      $result = $target === 'all' && $this->_install() ? TRUE : $result;
    }

    //JSONの出力
    $this->_outPutJson($result ? 200 : 400, array(
      'validation' => $this->error ? array('db_error' => $this->error) : $errors,
    ));
  }

  /**
   * インストール画面
   * @return void
   */
  public function index()
  {
    //インストールの必要があるか判定、必要なければ管理TOPへリダイレクト
    if ( ! $this->install_model->is_need_install())
    {
      redirect(site_url('admin'));
    }

    //リソースをロードしつつ画面を表示
    $this->link_files->enable_develop_mode();
    $this->link_files->add_file('dist/install.bundle.js');
    $this->load->view('admin/install');
  }

}
