<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"LoveHello",
    *     "name_underline"   =>"love_hello",
    *     "table_name"       =>"love_hello",
    *     "model_name"       =>"LoveHelloModel",
    *     "remark"           =>"打招呼",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-27 16:24:10",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\LoveHelloModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveHelloModel extends Model{

	protected $name = 'love_hello';//打招呼

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
