<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"ShopClass",
    *     "name_underline"   =>"shop_class",
    *     "table_name"       =>"shop_class",
    *     "model_name"       =>"ShopClassModel",
    *     "remark"           =>"店铺类型",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-26 09:28:14",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\ShopClassModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class ShopClassModel extends Model{

	protected $name = 'shop_class';//店铺类型

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
