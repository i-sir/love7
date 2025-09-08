<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"LoveSquare",
    *     "name_underline"   =>"love_square",
    *     "table_name"       =>"love_square",
    *     "model_name"       =>"LoveSquareModel",
    *     "remark"           =>"广场管理",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-22 15:29:20",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\LoveSquareModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveSquareModel extends Model{

	protected $name = 'love_square';//广场管理

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
