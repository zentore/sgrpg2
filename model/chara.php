<?php
require_once('model.php');

/**
 * Charaモデル
 *
 * @version 1.0.0
 * @author  M.Katsube <katsubemakito@gmail.com>
 */
class CharaModel extends Model{
  protected $tableName = 'Chara';  // 対象テーブル

  /**
   * 指定キャラクターの詳細情報を返却
   *
   * @param $id integer|array
   * @return array
   */
  function getCharaDetail($id){
    $list = is_array($id)? $id:[$id];
    
    // SQLを組み立てる
    $hatena = rtrim(str_repeat('?,', count($list)), ',');
    $sql = sprintf('SELECT * FROM Chara WHERE id in(%s)', $hatena);
    
    // SQLに渡す値を準備する
    $bind = [];
    for($i=0; $i<count($list); $i++){
      $name   = $i+1;
      $value  = (int)$list[$i];
     
       // 配列の最後に追加する
      $bind[] = ['name'=>$name, 'value'=>$value, 'type'=>PDO::PARAM_INT];
    }

    // SQLを実行する    
    $this->query($sql, $bind);
    
    // 実行結果をすべて取得
    $result = $this->fetchAll();

    return( array_values($result) );
  }
}
