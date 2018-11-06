<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 *
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_Lang $lang
 * @property Options_model $options_model
 */
class Options_station extends PH_Model
{

  /**
   * このクラスが扱うデータの定義表
   * @var array
   */
  private $FIELDS = array();

  /**
   * Options_station constructor.
   * @return void
   */
  public function __construct()
  {
    parent::__construct();

    //データベース・表示の相互変換定義をロード、プロパティに設定
    $this->config->load('phage_data_fields', TRUE);
    $this->FIELDS = $this->config->item('options_data_fields', 'phage_data_fields');

    //modelのロード
    $this->load->model('database/options_model');

    //libraryのロード
    $this->load->library('form_validation');
  }

  /**
   * 管理画面で扱うことが可能なデータを取得する
   * @return mixed
   */
  public function multiple()
  {
    return array(
      'fields' => $this->FIELDS,
      'id'=> 'id',
      'link' => 'key_name',
      'data' => $this->options_model->get_controllable_data()
    );
  }

  /**
   * 管理画面で扱うことが可能なデータ単体を取得する
   * @return mixed
   */
  public function get($id)
  {
    return array(
      'fields' => $this->FIELDS,
      'data' => $this->options_model->get_controllable($id)
    );
  }

  /**
   * $_POST['data']で指定されたデータを更新する
   * 更新内容は$_POST['data']内のキーをフィールド名, 値を値として更新する
   * $_POST['data']全項目の更新を試みるが、一つでも失敗したら更新内容は全てロールバックされる
   * 全更新が成功したら200, 失敗したら400でエラーメッセージを返す
   * @return array
   */
  public function set()
  {
    //更新成功可否
    $results = array('message' => array('all' => ''));

    //トランザクションの開始
    $this->db->trans_start();

    //post配列を基にDBを更新ループ
    foreach ((array)$this->input->post('data') as $field => $value)
    {
      //そもそも定義表に無い or controlがFALSEのデータは更新させない
      if ( ! isset($this->FIELDS[$field]['control']) || ! $this->FIELDS[$field]['control'])
      {
        $results[] = '存在しない項目のデータは更新できません';
        continue;
      }

      //エラーメッセージ項目の初期化
      $results['message'][$field] = '';

      //更新を試み、失敗したらエラーメッセージとモデルのステータスを変更する
      if ( ! $this->options_model->set($field, $value))
      {
        $this->error = FALSE;
        $results['message'][$field] = $this->lang->line('db_error');
      }
    }

    //トランザクションの完了
    $this->db->trans_complete();

    //エラーメッセージを返す
    return $results;
  }

}
