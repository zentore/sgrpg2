<?php
/**
 * ベースモデル
 *
 * @version 1.0.0
 * @author M.Katsube <katsubemakito@gmail.com>
 */
class Model{
  //-------------------------------------
  // プロパティ
  //-------------------------------------
  // DBの接続情報
  private $dsn  = 'mysql:dbname=sgrpg;host=127.0.0.1';
  private $user = 'senpai';
  private $pw   = 'indocurry';

  // DBとの接続管理用
  private $dbh  = null;
  private $sth  = null;

  // 対象テーブル（サブクラスが代入する）
  protected $tableName = null;

  // エラー管理（最後に発生したエラーメッセージを保管する）
  private $error = [
    'message' => null
  ];

  /**
   * コンストラクタ
   *
   * @param string $dsn 
   * @param string $user
   * @param string $pw
   * @return void
   */
  function __construct($dsn=null, $user=null, $pw=null){
    if($dsn  !== null) $this->dsn  = $dsn;
    if($user !== null) $this->user = $user;
    if($pw   !== null) $this->pw   = $pw;
  }

  /**
   * DBへ接続
   *
   * @return void
   */
  function connect(){
    $this->dbh = new PDO($this->dsn, $this->user, $this->pw);
    $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
  }

  /**
   * SQLを実行
   *
   * @param string $sql
   * @param array  $bind  [ ['name'=>xxx, 'value'=>xxx, 'type'=>xxx], [...], [...] ]
   * @param boolean
   */  
  function query($sql, $bind=null){
    // DBへ接続
    if( $this->dbh === null ){
      $this->connect();
     }

    $this->sth = $this->dbh->prepare($sql);
    
    if( $bind !== null && is_array($bind) ){
      for( $i=0; $i<count($bind); $i++ ){
        $name  = $bind[$i]['name'];
        $value = $bind[$i]['value'];
        $type  = $bind[$i]['type'];
        
        $this->sth->bindValue($name, $value, $type);
      }
    }
  
    return( $this->sth->execute() );
  }

  /**
   * 実行結果を取得
   *
   * @return array|false
   */  
  function fetch(){
    return( $this->sth->fetch(PDO::FETCH_ASSOC) );
  }

  /**
   * すべての実行結果を取得
   *
   * @return array
   */  
  function fetchAll(){
    $result = [];
    while( ($buff = $this->fetch(PDO::FETCH_ASSOC)) !== false ){
       $result[] = $buff;
    }

    return($result);

    // 以下のようにPDOの機能を利用しても同じ結果になる
    // return( $this->sth->fetchAll(PDO::FETCH_ASSOC) );
  }

  /**
   * トランザクションを開始
   */
  function begin(){
    // DBへ接続
    if( $this->dbh === null ){
      $this->connect();
     }

    $this->dbh->beginTransaction();
  }

  /**
   * コミット
   */
  function commit(){
    $this->dbh->commit();  
  }

  /**
   * ロールバック
   */
  function rollback(){
    $this->dbh->rollBack();
  }

  /**
   * idで検索した結果を返却
   *
   * @param integer $id
   * @return array
   */
  function getRecordById($id){
    $sql  = sprintf('SELECT * FROM %s WHERE id=:id', $this->tableName);
    $bind = [ ['name'=>':id', 'value'=>$id, 'type'=>PDO::PARAM_INT] ];
    $this->query($sql, $bind);
    
    return( $this->fetch() );
  }

  /**
   * エラーメッセージをセット
   * 
   * @param string $message
   */
  function setError($message){
    $this->error['message'] = $message;
  }
 
  /**
   * エラーメッセージを返却
   *
   * @return string
   */
  function getError(){
    return( $this->error['message'] );
  }
}

