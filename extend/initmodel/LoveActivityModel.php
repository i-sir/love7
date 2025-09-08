<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"LoveActivity",
    *     "name_underline"   =>"love_activity",
    *     "table_name"       =>"love_activity",
    *     "model_name"       =>"LoveActivityModel",
    *     "remark"           =>"活动管理",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-22 10:03:06",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\LoveActivityModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveActivityModel extends Model{

	protected $name = 'love_activity';//活动管理

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
