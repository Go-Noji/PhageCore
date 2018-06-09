<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 */
/**
 * htmlで読み込むべき<script src="hoge.js"></script>のhoge.js部分を生成する
 * htmlで読み込むべき<link rel="stylesheet" type="text/css" href="hoge.css"></script>のhoge.css部分を生成する
 * コントローラーで読み込み、コントローラー名から読み込むべきjsを大方決定する
 * その後、コントローラーごとに追加したいjsを選択する等して、最終的にjsとcssへのリンクを出力する
 * jsに関してはヘッダーで読み込む必要があるものとbody終了タグ直前で読み込む必要があるものを分けて出力する
 * Class LinkFiles
 * @property CI_Loader $load;
 * @property CI_Config $config;
 */
class Link_files {

  /**
   * Codeigniterの機能にアクセスするためのポイント
   * @var CI_Controller $CI
   */
  private $CI;

  /**
   * 勝手に読み込みたいリンクファイルを読み込むキー
   * キーごとの設定はconfigフォルダのauto_link.phpを参照のこと
   * これにより、大体の読み込むべきcssとjsを区別する
   * @var string $auto_loader_key
   */
  private $auto_loader_key;

  /**
   * このフラグをTRUEにすると全てのファイルが毎回キャッシュされずに読み込まれる
   * @var bool
   */
  private $develop_flg = FALSE;

  /**
   * <link href="この中身の配列">
   * @var array $stylesheets
   */
  private $stylesheets = array();

  /**
   * <script src="この中身の配列">
   * <head>内で読み込む必要のある（アナリティクスとか）専用
   * @var array $header_scripts
   */
  private $header_scripts = array();

  /**
   * <link href="この中身の配列">
   * </body>直前に読み込まれるものを想定
   * @var array $footer_scripts
   */
  private $footer_scripts = array();

  /**
   * 結果配列
   * array(
   *  'style' => array('hoge.css', 'fuga.css'),
   *   'scripts' => array(
   *    'head' => array('hoge.js'),
   *    'foot' => array('huga.js', 'piyo.js')
   *  )
   * )
   * みたいな値が保存される
   * @var array
   */
  private $result = array();

  /**
   * urlヘルパーのロード
   * @return void
   */
  public function __construct()
  {
    $this->CI =& get_instance();
    $this->CI->load->helper('url');
  }

  /**
   * 結果を生成する
   * 結果を$resultプロパティに保存し、同じものを返す
   * @return array
   */
  private function _get_links()
  {
    //その前にオートロードを割り込ませる
    $this->CI->config->load('auto_link', TRUE);
    if ($this->auto_loader_key)
    {
      $configs = $this->CI->config->item($this->auto_loader_key, 'auto_link');
      if ($configs)
      {
        $stylesheets = $this->stylesheets;
        $header_scripts = $this->header_scripts;
        $this->stylesheets = array();
        $this->header_scripts = array();
        foreach ((array)$configs as $config)
        {
          $this->add_file($config, TRUE, $this->develop_flg);
        }
        $this->stylesheets = array_merge($this->stylesheets, $stylesheets);
        $this->header_scripts = array_merge($this->header_scripts, $header_scripts);
      }
    }

    $this->result = array(
      'style' => $this->stylesheets,
      'script' => array(
        'head' => $this->header_scripts,
        'foot' => $this->footer_scripts
      )
    );
    return $this->result;
  }

  /**
   * ランダムな文字列を値としたquery busting文字列を生成して返す
   * @return string
   */
  private function _create_unique_query()
  {
    return '?rand='.(string)rand().(string)uniqid();
  }

  /**
   * 開発者モードを有効にする
   * 有効にすると全てのファイルが毎回キャッシュされずに読み込まれる
   * @return void
   */
  public function enable_develop_mode()
  {
    $this->develop_flg = TRUE;
  }

  /**
   * 勝手に読み込みたいリンクファイルグループを表すキーを設定する
   * キーごとの設定はconfigフォルダのauto_link.phpを参照のこと
   * このメソッドを呼び出すと$resultが初期化される(get系のメソッド実行時に再計算される)
   * @param string $key
   * @return void
   */
  public function set_auto_loader_key($key)
  {
    //初期化
    $this->result = array();

    //値のセット
    $this->auto_loader_key = $key;
  }

  /**
   * このクラスによって自動的に追加されたファイル以外に読み込ませたいファイルがある場合に使用する
   * cssファイルは自動的に$stylesheetsへ追加
   * jsファイルは第二引数が未設定なら$footer_scriptsへ、TRUEに設定してあれば$header_scriptsへ追加
   * $cash_bustingをTRUEに設定するとファイルをキャッシュさせないための乱数をクエリ文字列として仕込む
   * このメソッドを呼び出すと$resultが初期化される(get系のメソッド実行時に再計算される)
   * @param string $file
   * @param bool $head_flg
   * @param bool $cash_busting
   */
  public function add_file($file, $head_flg = FALSE, $cash_busting = FALSE)
  {
    //初期化
    $this->result = array();

    //追加処理
    $query = ! $this->develop_flg && ! $cash_busting ? '' : $this->_create_unique_query();
    if(preg_match("/.*\.css$/", $file))
    {
      $this->stylesheets[] = base_url().'css/'.$file.$query;
    }
    elseif(preg_match("/.*\.js$/", $file))
    {
      if($head_flg)
      {
        $this->header_scripts[] = base_url().'js/'.$file.$query;
      }
      else
      {
        $this->footer_scripts[] = base_url().'js/'.$file.$query;
      }
    }
  }

  /**
   * このクラスによって作成されたstyleパス配列を返す
   * @return array
   */
  public function get_style()
  {
    //値が生成されていなかったら生成
    $result = $this->result ? $this->result : $this->_get_links();

    //styleを返す
    return isset($result['style']) ? $result['style'] : array();
  }

  /**
   * このクラスによって作成されたscriptパス配列を返す
   * 通常はbodyタグ直前で読み込むべきモノを返すが、
   * $headをTRUEにするとheadタグで読み込むべきものが返る
   * @param bool $head
   * @return array
   */
  public function get_script($head = FALSE)
  {
    //対象の設定
    $target = $head ? 'head' : 'foot';

    //値が生成されていなかったら生成
    $result = $this->result ? $this->result : $this->_get_links();

    //対象のscriptを返す
    return isset($result['script'][$target]) ? $result['script'][$target] : array();
  }

}
