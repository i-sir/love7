<?php

namespace initmodel;

/**
    * @AdminModel(
    *     "name"             =>"ShopManageOrder",
    *     "name_underline"   =>"shop_manage_order",
    *     "table_name"       =>"shop_manage_order",
    *     "model_name"       =>"ShopManageOrderModel",
    *     "remark"           =>"管理费订单管理",
    *     "author"           =>"",
    *     "create_time"      =>"2024-08-26 10:06:12",
    *     "version"          =>"1.0",
    *     "use"              => new \initmodel\ShopManageOrderModel();
    * )
    */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class ShopManageOrderModel extends Model{

	protected $name = 'shop_manage_order';//管理费订单管理

	//软删除
	protected $hidden            = ['delete_time'];
	protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
