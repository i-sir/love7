<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"ShopEvaluate",
    *     "name_underline"   =>"shop_evaluate",
    *     "table_name"       =>"shop_evaluate",
    *     "model_name"       =>"ShopEvaluateModel",
    *     "remark"           =>"店铺评价",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-26 10:56:55",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\ShopEvaluateModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class ShopEvaluateModel extends Model{

	protected $name = 'shop_evaluate';//店铺评价

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
