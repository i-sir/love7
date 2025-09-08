<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"Shop",
    *     "name_underline"   =>"shop",
    *     "table_name"       =>"shop",
    *     "model_name"       =>"ShopModel",
    *     "remark"           =>"店铺管理",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-26 09:27:36",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\ShopModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class ShopModel extends Model{

	protected $name = 'shop';//店铺管理

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
