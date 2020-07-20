<?php
require_once('model.php');

/**
 * Userモデル
 *
 * @version 1.0.0
 * @author M.Katsube <katsubemakito@gmail.com>
 */
class UserModel extends Model{
  public $uid = null;
  public $token = null;

  // 対象テーブル
  protected $tableName = 'User';

  // レコードの初期値
  private $defaultValue = [
    ['name'=>':lv',    'value'=>1,    'type'=>PDO::PARAM_INT],
    ['name'=>':exp',   'value'=>1,    'type'=>PDO::PARAM_INT],
    ['name'=>':money', 'value'=>3000, 'type'=>PDO::PARAM_INT],
    ['name'=>':token', 'value'=>null, 'type'=>PDO::PARAM_STR]
  ];

  // トークンの文字列長
  const TOKEN_LENGTH = 32;


  /**
   * トークンの書式が正しいかチェック
   *
   * @return integer|false
   */
  static function getTokenfromQuery(){
    $token = isset($_GET['token'])?  $_GET['token']:null;

    if( ($token === null) || (!is_string($token)) || (strlen($token) !== self::TOKEN_LENGTH) ){
      return(false);
    }
    else{
      return($token);
    }
  }

  /**
   * ユーザーを追加
   *
   * @return integer|false
   */
  function join(){
    // トークンを生成
    $token = $this->_getToken();

    // ユーザーを追加
    $sql1 = 'INSERT INTO User(lv, exp, money, token) VALUES(:lv, :exp, :money, :token)';
    $this->defaultValue[3]['value'] = $token;
    $this->query($sql1, $this->defaultValue);

    // AUTO_INCREMENTしたユーザーIDを取得
    $sql2 = 'SELECT LAST_INSERT_ID() as id';
    $this->query($sql2);
    $buff = $this->fetch();

    // プロパティに保存
    $this->token = $token;
    $this->uid = $buff['id'];    
  }

  /**
   * トークンからユーザーIDを特定
   *
   * @param string $token
   * @return int|false
   */
  function getUserIdByToken($token){
    $sql  = 'SELECT id FROM User WHERE token=:token';
    $bind = [ ['name'=>':token', 'value'=>$token, 'type'=>PDO::PARAM_STR] ];

    $this->query($sql, $bind);
    $buff = $this->fetch();
    
    if( $buff !== false ){
      return($buff['id']);
    }
    else{
      return(false);
    }
  }

  /**
   * 所持金を返却
   *
   * @param integer $uid
   * @return integer|false
   */
  function getMoney($uid){
    $buff = $this->getRecordById($uid);
    if( $buff !== false ){
      return($buff['money']);
    }
    else{
      return(false);
    }
  }

  /**
   * 所持金を利用する（減らす）
   *
   * @param integer $uid
   * @param integer $value
   * @param boolean [$safety=true]
   * @return boolean 
   */
  function useMoney($uid, $value, $safety=true){
    // 残高がマイナスにならないかチェック
    if( $safety ){
      $money = $this->getMoney($uid);
      if( ($money === false) || ($money-$value) < 0 ){
         $this->setError('The balance is not enough');
        return(false);
      }
    }

    // 残高を減らす
    $sql  = 'UPDATE User SET money=money-:price WHERE id=:userid';
    $bind = [
      ['name'=>':price',  'value'=>$value, 'type'=>PDO::PARAM_INT],
      ['name'=>':userid', 'value'=>$uid,   'type'=>PDO::PARAM_INT]
     ];

    return( $this->query($sql, $bind) );
  }

  /**
   * 所有しているキャラクターを返却する
   *
   * @param integer $uid
   * @return array
   */
  function getChara($uid){
    $sql  = 'SELECT distinct chara_id FROM UserChara WHERE user_id=:userid';
    $bind = [
      ['name'=>':userid', 'value'=>$uid, 'type'=>PDO::PARAM_INT]
    ];
    // SQLを実行
    $this->query($sql, $bind);

    // 実行結果をすべて取得
    $buff = $this->fetchAll();
    
    $result = [];
    for($i=0; $i<count($buff); $i++){
      $result[] = $buff[$i]['chara_id'];
    }

    return( $result );
  }


  /**
   * キャラクターを所有する
   *
   * @param integer $uid
   * @param integer $charaid
   * @return boolean
   */
  function addChara($uid, $charaid){
    $sql  = 'INSERT INTO UserChara(user_id, chara_id) VALUES(:userid,:charaid)';
    $bind = [
      ['name'=>':userid',  'value'=>$uid,     'type'=>PDO::PARAM_INT],
      ['name'=>':charaid', 'value'=>$charaid, 'type'=>PDO::PARAM_INT]
    ];

    return( $this->query($sql, $bind) );
  }

  /**
   * トークンを作成する
   *
   * @param int $len
   * @return string
   */
  private function _getToken($len=null){
    if( $len === null ){
      $len = self::TOKEN_LENGTH;
    }
    return( substr(bin2hex(random_bytes($len)), 0, $len) );
  }
}
