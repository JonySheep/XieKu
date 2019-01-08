<?php
/**
 * File: common.php
 * User: XljBearSoft
 * Date: 2017-07-17
 * Time: 15:19
 */
function CreateInfoPage($title,$info,$button,$back){
    $infoPage = new \app\admin\controller\Info();
    return $infoPage->Info_page($title,$info,$button,$back);
}

function consoleLog($param)
{
	echo "<script> console.log(".json_encode($param)."); </script>";
}