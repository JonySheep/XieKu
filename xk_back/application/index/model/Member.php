<?php
/**
 * File: member.php
 * User: XljBearSoft
 * Date: 2017-07-07
 * Time: 17:32
 */
namespace app\index\model;
use think\Model;
class Member extends Model{
    public function chats(){
        return $this->hasMany("Chat","userid");
    }
}