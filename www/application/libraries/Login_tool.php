<?php
/**
 * @copyright oddcodes All Rights Reserved
 * @license https://opensource.org/licenses/mit-license.html MIT License
 * @author Go Noji <gisosyadfe@gmail.com>
 */
/**
 * ログイン情報を扱うためのライブラリ
 * ログインのための情報をセットしたり、ログイン済かどうかを判定する
 * ログイン制限回数を設定する
 * Class Login_tool
 * @property CI_Loader $load;
 * @property CI_Config $config;
 */
class Login_tool
{

  /**
   * CodeIgniterへのアクセス基底ポイント
   * @var object
   */
  private $CI;

  /**
   * 管理画面に対してなのか、ユーザーに対してなのか等を判別するキー文字列
   * @var string
   */
  private $key;

  /**
   * 制限回数
   * @var int
   */
  private $limit_num;

  /**
   * 解除に必要な時間（秒）
   * ログイン制限が始まった時刻+この値がログイン制限解除時刻となる
   * @var int
   */
  private $release_time;

  /**
   * アクセス制限を表すセッションキー
   * @var string
   */
  private $count_key;

  /**
   * ログイン拒否兼開放時間を表すセッションキー
   * @var string
   */
  private $release_key;

  /**
   * コンストラクタ
   * $paramの第一引数に文字列としてキーを渡すことで
   * ログインの種類(管理者やユーザー等)を判別できる
   * 第二引数を設定すると失敗許容回数に、
   * 第三引数を設定すると解放までの時間（秒）になる
   * @param array $param
   * @return void
   */
  public function __construct(array $param)
  {
    //リソースのロード
    $this->CI =& get_instance();

    //セッションライブラリのロード
    $this->CI->load->library('session');

    //ファイルヘルパーのロード
    $this->CI->load->helper(array('file'));

    //キー文字列が設定されていなかったら勝手に'user'という文字列を設定する
    $this->key = isset($param[0]) && is_string($param[0]) ? $param[0] : 'user';

    //ログイン制限に関する二つのセッションキー名を設定
    $this->count_key = $this->key.'_limit_count';
    $this->release_key = $this->key.'_release_key';

    //制限回数をセット
    $this->limit_num = isset($param[1]) ? (int)$param[1] : 0;

    //開放時間をセット
    $this->release_time = isset($param[2]) ? (int)$param[2] : 0;

    //もしセッションにログイン回数制限用データが無かったら作成する
    $_SESSION[$this->count_key] = isset($_SESSION[$this->count_key]) ? $_SESSION[$this->count_key] : 0;

    //ログイン制限ファイルが存在する且つ解除時間を過ぎていたら制限ファイルを削除
    if (isset($_SESSION[$this->release_key]) && time() >= $this->get_release(TRUE))
    {
      unset($_SESSION[$this->release_key]);
    }
  }

  /**
   * ログイン制限カウントを$countの内容で書き換える
   * @param $count int
   * @return void
   */
  private function _set_count($count)
  {
    $_SESSION[$this->count_key] = $count;
  }

  /**
   * ログイン制限兼開放時間を設定
   * 言い換えれば、ログイン制限開始
   * @return void
   */
  private function _set_release_time()
  {
    $_SESSION[$this->release_key] = time() + $this->release_time;
  }

  /**
   * ログインしているかどうか返す
   * @return bool
   */
  public function is_login()
  {
    return isset ($_SESSION[$this->key]) ? TRUE : FALSE;
  }

  /**
   * ログインが成功したときに呼ぶ
   * ログイン制限回数をリセットする
   * @param int $id
   * @return void
   */
  public function login($id)
  {
    session_regenerate_id(TRUE);

    //ログイン制限リセット
    $this->_set_count($this->limit_num);

    //ログインデータをセッションに挿入
    $_SESSION[$this->key] = $id;
  }

  /**
   * セッションを壊してログアウトさせる
   * ただし、そもそもログインしていない場合はセッションを壊させない
   * @return void
   */
  public function logout()
  {
    if ( ! $this->is_login($this->key))
    {
      return;
    }

    setcookie(session_name(), '', 1);
    session_destroy();
  }

  /**
   * ログイン制限設定が有効か無効かを返す
   * @return bool
   */
  public function is_limiter()
  {
    return (bool)$this->limit_num;
  }

  /**
   * ログイン制限中だったらTRUE,それ以外はFALSEを返す
   * @return bool
   */
  public function is_limit()
  {
    return isset($_SESSION[$this->release_key]);
  }

  /**
   * 残り失敗許容回数を返す
   * $reverseがTRUEの場合は現在の失敗回数を返す
   * @param bool $reverse
   * @return int
   */
  public function get_limit($reverse = FALSE)
  {
    if ( ! isset($_SESSION[$this->count_key]))
    {
      return 0;
    }

    return $reverse ? $_SESSION[$this->count_key] : $this->limit_num - $_SESSION[$this->count_key];
  }

  /**
   * 現在一回も失敗していないかどうかを返す
   * @return bool
   */
  public function is_no_failure()
  {
    return $this->get_limit() === $this->limit_num && ! $this->is_limit();
  }

  /**
   * ログイン制限解除までの残り時間（秒）を返す
   * そもそもログイン制限中以外は0を返す
   * $reverseがTRUEの場合は制限解除予定時刻を返す
   * @param bool $reverse
   * @return int
   */
  public function get_release($reverse = FALSE)
  {
    if ( ! isset($_SESSION[$this->release_key]))
    {
      return 0;
    }

    return $reverse ? $_SESSION[$this->release_key] : $_SESSION[$this->release_key] - time();
  }

  /**
   * ログイン中のIDを取得する
   * 失敗した際（そもそもログインしていない等）は-1が返る
   * @return int
   */
  public function get_login_id()
  {
    return isset ($_SESSION[$this->key]) ? $_SESSION[$this->key] : -1;
  }

  /**
   * ログインが失敗した際に呼ぶ
   * 失敗回数をインクリメントする
   * @return void
   */
  public function set_failure()
  {
    //ログイン回数が最初から0 or 既に制限中の場合は何もしない
    if ( ! $this->limit_num || $this->is_limit())
    {
      return;
    }

    //インクリメントした値を取得し上書きする（下限は0）
    $limit = $this->get_limit(TRUE) + 1;

    //まだ失敗許容回数に届いていない場合は数をインクリメントして登録
    if ($limit < $this->limit_num)
    {
      $this->_set_count($limit);
      return;
    }

    //届いていた場合は0にセットし直してログイン不可ファイルを作成
    $this->_set_release_time();
    $this->_set_count(0);
  }

}
