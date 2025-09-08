<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"LoveActivityLog",
    *     "name_underline"   =>"love_activity_log",
    *     "table_name"       =>"love_activity_log",
    *     "model_name"       =>"LoveActivityLogModel",
    *     "remark"           =>"报名管理",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-22 10:03:44",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\LoveActivityLogModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveActivityLogModel extends Model{

	protected $name = 'love_activity_log';//报名管理

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
