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
class PH_Controller extends CI_Controller
{

  /**
   * バリデーションメッセ―ジが格納される
   * @var array
   */
  protected $validation_messages = array();

  /**
   * PH_Controller constructor.
   */
  public function __construct()
  {
    parent::__construct();

    //セッションをスタートさせる
    $this->load->library('session');

    //設定の読み込み
    $this->load->config('phage_config');

    //バリデーションライブラリのロード
    $this->load->library('form_validation');
  }

  /**
   * $modelで指定されたStationモデルをロードし、$this->で参照するモデル名を返す
   * 失敗すると空文字を返す
   * @param string $model
   * @return string
   */
  private function _load_station($model)
  {
    //モデル名の取得
    $model_name = lcfirst($model.'_station');

    //モデルファイルが存在しなかったら空文字を返す
    if ( ! file_exists(APPPATH.'models/'.$this->config->item('station_model_directory').'/'.ucfirst($model).'_station.php'))
    {
      return '';
    }

    //対応するModelをmodels/下にあるstationディレクトリからロード
    //stationディレクトリの名前はconfigs/phage_config.phpに'station_model_directory'の名前で設定されている
    $this->load->model($this->config->item('station_model_directory').'/'.$model_name);

    //モデル名を返す
    return $model_name;
  }

  /**
   * $model_name->$methodがこのクラスの$actionで呼ばれるべきメソッドか判定する
   * $actionにはこのメソッドを呼び出したメソッド名(call, mutation等)
   * $model_nameにはロード済の$this->この部分
   * $methodには呼び出そうとしている$this->$model_name->この部分
   * をそれぞれ設定する
   * @param string $action
   * @param string $model_name
   * @param string $method
   * @return bool
   */
  private function _is_method_type($action, $model_name, $method)
  {
    return call_user_func_array(array($this->$model_name, 'is_method_type'), array($method, $action));
  }

  /**
   * call(), またはmutation()を実行する権限があるか判定する
   * 継承先でオーバーライドして使う
   * TRUEを返すことで実行可能・FALSEを返すことで実行不可
   * $actionは'call'か'mutation'
   * $modelは呼び出そうとしているStationモデル
   * $methodは呼び出そうとしているStationモデルのメソッド名が入る
   * @param string $action
   * @param string $model
   * @param string $method
   * @return bool
   */
  protected function _is_qualification($action, $model, $method)
  {
    return TRUE;
  }

  /**
   * JSONを吐き出してスクリプトを終了させる
   * @param int $status
   * @param array $data
   * @return void
   */
  protected function _output_json($status = 200, $data = array())
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
   * $model_nameと$methodによって指定されたstationディレクトリのクラス->メソッドを叩く
   * メソッドの引数には$_POST['augments']を分解したものが割り当てられる
   * @param string $model_name
   * @param string $method
   */
  protected function _call_method($model_name, $method)
  {
    //呼ぶ
    $result = call_user_func_array(array($this->$model_name, $method), (array)$this->input->post('arguments'));

    //JSON出力
    $this->_output_json($this->$model_name->is_error() ? 400 : 200, $result);
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

    //バリデーション設定を読み込む
    $this->config->load('form_validation', TRUE);

    //$fieldsに対して必要な設定をロードしてセットする
    foreach ((array)$fields as $field => $value)
    {
      //一旦設定を変数に格納
      $rule = $this->config->item($model.'_'.$field,'form_validation');

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
      $this->validation_messages = validation_errors(' ', ' ');
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
    //更新資格があるか確認する
    if ( ! $this->_is_qualification('call', $model, $method))
    {
      $this->_output_json(400, array('message' => $this->lang->line('bad_access')));
    }

    //モデルのロード
    $model_name = $this->_load_station($model);

    //ロードに失敗したら終了
    if ($model_name === '')
    {
      $this->_output_json(400, array('message' => $this->lang->line('bad_access')));
    }

    //callタイプのメソッドであるか確認する
    if ( ! $this->_is_method_type('call', $model_name, $method))
    {
      $this->_output_json(400, array('message' => $this->lang->line('bad_access')));
    }

    $this->_call_method($model_name, $method);
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
    //更新資格があるか確認する
    if ( ! $this->_is_qualification('mutation', $model, $method))
    {
      $this->_output_json(400, array('message' => $this->lang->line('bad_access')));
    }

    //そもそも更新データが確認できない
    if ( ! $this->input->post('data'))
    {
      $this->_output_json(400, array('message' => $this->lang->line('empty_data')));
    }

    //モデルのロード
    $model_name = $this->_load_station($model);

    //ロードに失敗したら終了
    if ($model_name === '')
    {
      $this->_output_json(400, array('message' => $this->lang->line('bad_access')));
    }

    //mutationタイプのメソッドであるか確認する
    if ( ! $this->_is_method_type('mutation', $model_name, $method))
    {
      $this->_output_json(400, array('message' => $this->lang->line('bad_access')));
    }

    //バリデーションが失敗した場合はエラーメッセージをステータスコード400とともに返却
    if ( ! $this->_validation($this->input->post('data'), lcfirst($model)))
    {
      $this->_output_json(400, array('message' => $this->validation_messages));
    }

    //$this->_call_method()から対象のモデルメソッドを叩く
    $this->_call_method($model_name, $method);
  }

}
