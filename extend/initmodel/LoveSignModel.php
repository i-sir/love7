<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"LoveSign",
    *     "name_underline"   =>"love_sign",
    *     "table_name"       =>"love_sign",
    *     "model_name"       =>"LoveSignModel",
    *     "remark"           =>"签到",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-22 10:55:41",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\LoveSignModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveSignModel extends Model{

	protected $name = 'love_sign';//签到

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
