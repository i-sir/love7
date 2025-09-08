<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"LoveComment",
    *     "name_underline"   =>"love_comment",
    *     "table_name"       =>"love_comment",
    *     "model_name"       =>"LoveCommentModel",
    *     "remark"           =>"评论管理",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-24 09:38:18",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\LoveCommentModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveCommentModel extends Model{

	protected $name = 'love_comment';//评论管理

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
