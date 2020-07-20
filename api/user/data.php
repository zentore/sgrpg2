<?php
/**
 * MySQLに接続しデータを取得する
 *
 */

// 以下のコメントを外すと実行時エラーが発生した際にエラー内容が表示される
// ini_set('display_errors', 'On');
// ini_set('error_reporting', E_ALL);

//-------------------------------------------------
// ライブラリ
//-------------------------------------------------
require_once("../util.php");
require_once("../../model/user.php");
require_once("../../model/chara.php");

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
try{
  $user = new UserModel();

  // トークンをIDに変換
  $uid  = $user->getUserIdByToken($token);
  if( $uid === false ){
    sendResponse(false, 'Not Found user');
    exit(1);
  }

  // ユーザーの基本情報を取得
  $data = $user->getRecordById($uid);  // 基本情報
  $mychara = $user->getChara($uid);    // 所有キャラクター

  // 所有しているキャラを追加
  $chara = new CharaModel();
  $buff  = $chara->getCharaDetail($mychara);  // キャラ名を持ってくる
  $data['chara'] = $buff;
}
catch( PDOException $e ) {
  sendResponse(false, 'Database error: '.$e->getMessage());
  exit(1);
}

//-------------------------------------------------
// 実行結果を返却
//-------------------------------------------------
sendResponse(true, $data);

