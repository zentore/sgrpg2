<?php
require_once('model.php');

/**
 * Gachaモデル
 *
 * @version 1.0.0
 * @author  M.Katsube <katsubemakito@gmail.com>
 */
class GachaModel extends Model{
  static public $PRICE = 300;   // ガチゃ1回の価格
  private $MAX_CHARA = 10;      // キャラの総数

  /**
   * キャラクターを1体抽選する
   *
   * @return integer
   */
  function drop(){
    $num  = random_int(1, $this->MAX_CHARA);
    return($num);
  }
}
