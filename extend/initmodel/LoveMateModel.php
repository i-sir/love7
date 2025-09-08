<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"LoveMate",
    *     "name_underline"   =>"love_mate",
    *     "table_name"       =>"love_mate",
    *     "model_name"       =>"LoveMateModel",
    *     "remark"           =>"牵线管理",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-27 18:37:31",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\LoveMateModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveMateModel extends Model{

	protected $name = 'love_mate';//牵线管理

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
