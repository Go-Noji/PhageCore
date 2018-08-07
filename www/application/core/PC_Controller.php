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
 * @property Login_tool $login_tool
 */
class PC_Controller extends CI_Controller
{

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
   * $modelクラスの$methodを呼ぶ
   * $modelクラスはPC_Modelを継承している必要がある(is_errorメソッドを使用するため)
   * @param $model
   * @param $method
   */
  public function call($model, $method)
  {
    //$modelの先頭文字を小文字にする
    $model = lcfirst($model.'_station');

    //対応するModelをmodels/下にあるstationディレクトリからロード
    //stationディレクトリの名前はconfigs/phage_config.phpに'station_model_directory'の名前で設定されている
    $this->load->model($this->config->item('station_model_directory').'/'.$model);

    //呼ぶ
    $result = call_user_func_array(array($this->$model, $method), (array)$this->input->post('argument'));

    //JSON出力
    $this->_outPutJson($this->$model->is_error() ? 400 : 200, $result);
  }

}