<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Output $output
 * @property CI_Input $input
 * @property CI_Config $config
 * @property CI_Session $session
 * @property CI_Lang $lang
 * @property CI_Form_validation $form_validation
 * @property Login_tool $login_tool
 */
class PC_Controller extends CI_Controller
{

  /**
   * バリデーションメッセ―ジが格納される
   * @var array
   */
  protected $validation_messages = array();

  /**
   * PC_Controller constructor.
   */
  public function __construct()
  {
    parent::__construct();

    //セッションをスタートさせる
    $this->load->library('session');

    //設定の読み込み
    $this->load->config('phage_config');

    //バリデーションライブラリのロード
    $this->load->library('for_validation');
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
   * $modelと$methodによって指定されたstationディレクトリのクラス->メソッドを叩く
   * メソッドの引数には$_POST['augments']を分解したものが割り当てられる
   * @param $model
   * @param $method
   */
  protected function _call_method($model, $method)
  {
    //対応するModelをmodels/下にあるstationディレクトリからロード
    //stationディレクトリの名前はconfigs/phage_config.phpに'station_model_directory'の名前で設定されている
    $this->load->model($this->config->item('station_model_directory').'/'.lcfirst($model.'_station'));

    //呼ぶ
    $result = call_user_func_array(array($this->$model, $method), (array)$this->input->post('arguments'));

    //JSON出力
    $this->_outPutJson($this->$model->is_error() ? 400 : 200, $result);
  }

  /**
   * 渡された$fieldsを元にバリデーションを実行する
   * 「$model.'_'.$fieldsの添え字」という名前の設定をconfig/form_validation.phpから読み込んで実行する
   * 実行されたバリデーションが一つでも失敗したらFALSEを返す
   * バリデーションの結果メッセージは$fieldsの項目ごとに$this->validation_messageへ格納される
   * @param array $fields
   * @param string $model
   * @return bool
   */
  protected function _validation($fields, $model)
  {
    //返却bool値
    $result = TRUE;

    //$this->validation_messagesの初期化
    $this->validation_messages = array();

    //念のためバリデーションを初期化
    $this->form_validation->reset_validation();

    //バリデーション対象を$_POSTから$dataへ変更
    $this->form_validation->set_data($fields);

    //$fieldsに対して必要な設定をロードしてセットする
    foreach ((array)$fields as $field => $value)
    {
      //一旦設定を変数に格納
      $rule = $this->config->item($model.'_'.$field);

      //設定が存在した場合のみバリデーション対象とする
      if ( ! isset($rule['field']) || ! isset($rule['label']) || ! isset($rule['rules']))
      {
        continue;
      }

      //errorsが存在すればそれもセットしておく
      $this->form_validation->set_rules($rule['field'], $rule['label'], $rule['rules'], isset($rule['errors']) ? $rule['errors'] : array());

      //バリデーション実行
      $result = $this->form_validation->run() ? $result : FALSE;

      //バリデーションメッセージを判定した値の添え字名で登録
      $this->validation_messages[$field] = validation_errors();
    }

    //結果を返却
    return $result;
  }

  /**
   * $modelクラスの$methodを呼ぶ
   * $modelクラスはPC_Modelを継承している必要がある(is_errorメソッドを使用するため)
   * @param $model
   * @param $method
   */
  public function call($model, $method)
  {
    $this->_call_method($model, $method);
  }

  /**
   * 基本的に$callと同じ動きをするが、処理前に$_POST['data']に対してバリデーションが実行される
   * 全てのバリデーションを通過した場合のみデータが更新される
   * バリデーション失敗メッセージは返却JSONのmessageへ格納される
   * バリデーションが全て成功すると_call_method()が呼ばれ, 一つでも失敗すると400が返る
   * @param $model
   * @param $method
   */
  public function mutation($model, $method)
  {
    //そもそも更新データが確認できない
    if ( ! $this->input->post('data'))
    {
      $this->_outPutJson(400, array('message' => array('all' => $this->lang->line('empty_data'))));
    }

    //バリデーションが失敗した場合はエラーメッセージをステータスコード400とともに返却
    if ( ! $this->_validation($this->input->post('data'), lcfirst($model.'_station')))
    {
      $this->_outPutJson(400, array('message' => array_merge(array('all' => ''), $this->validation_messages)));
    }

    //$this->_call_method()から対象のモデルメソッドを叩く
    $this->_call_method($model, $method);
  }

}