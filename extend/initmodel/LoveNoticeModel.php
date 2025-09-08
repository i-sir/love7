<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"LoveNotice",
    *     "name_underline"   =>"love_notice",
    *     "table_name"       =>"love_notice",
    *     "model_name"       =>"LoveNoticeModel",
    *     "remark"           =>"系统通知",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-28 09:53:31",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\LoveNoticeModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveNoticeModel extends Model{

	protected $name = 'love_notice';//系统通知

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
