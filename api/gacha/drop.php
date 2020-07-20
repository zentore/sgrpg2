<?php
/**
 * ガチャAPI
 *
 */

// 以下のコメントを外すと実行時エラーが発生した際にエラー内容が表示される
// ini_set('display_errors', 'On');
// ini_set('error_reporting', E_ALL);

//-------------------------------------------------
// ライブラリ
//-------------------------------------------------
require_once('../util.php');
require_once('../../model/user.php');
require_once('../../model/gacha.php');
require_once('../../model/chara.php');

//-------------------------------------------------
// 引数を受け取る
//-------------------------------------------------
$token = UserModel::getTokenfromQuery();

if( !$token ){
  sendResponse(false, 'Invalid token');
  exit(1);
}


//-------------------------------------------------
// SQLを実行
//-------------------------------------------------
$gacha = new GachaModel();
$chara_id = $gacha->drop();  // 抽選のみ

try{
  $user = new UserModel();

  // ユーザーIDを取得
  $uid = $user->getUserIdByToken($token);
  if( $uid === false ){
    sendResponse(false, 'Not Found User');
    exit(1);
  }

  // トランザクション開始
  $user->begin();

  // お金を消費
  $ret = $user->useMoney($uid, GachaModel::$PRICE);
  if( $ret === false ){
    sendResponse(false, $user->getError());
    exit(1);
  }

  // キャラクターを所有  
  $user->addChara($uid, $chara_id);
  $user->commit();
}
catch( PDOException $e ) {
  $user->rollback();
  sendResponse(false, 'Database error1: '.$e->getMessage());
  exit(1);
}

//-------------------------------------------------
// 実行結果を返却
//-------------------------------------------------
try{
  $chara = new CharaModel();
  $buff = $chara->getRecordById($chara_id);
}
catch( PDOException $e ) {
  sendResponse(false, 'Database error2: '.$e->getMessage());
  exit(1);
}

// データが0件
if( $buff === false ){
  sendResponse(false, 'System Error');
}
// データを正常に取得
else{
  sendResponse(true, $buff);
}

