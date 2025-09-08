<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"LoveEmotion",
    *     "name_underline"   =>"love_emotion",
    *     "table_name"       =>"love_emotion",
    *     "model_name"       =>"LoveEmotionModel",
    *     "remark"           =>"情感测试",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-21 18:15:46",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\LoveEmotionModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveEmotionModel extends Model{

	protected $name = 'love_emotion';//情感测试

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
