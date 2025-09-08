<?php

namespace app\admin\validate;

use think\Validate;


/**
    * @AdminModel(
    *     "name"             =>"MemberVipTwo",
    *     "name_underline"   =>"member_vip_two",
    *     "table_name"       =>"member_vip",
    *     "validate_name"    =>"MemberVipTwoValidate",
    *     "remark"           =>"用户等级",
    *     "author"           =>"",
    *     "create_time"      =>"2025-03-13 17:04:37",
    *     "version"          =>"1.0",
    *     "use"              =>   $this->validate($params, MemberVipTwo);
    * )
    */

class MemberVipTwoValidate extends Validate
{

protected $rule = ['name'=>'require',
'day'=>'require',
];




protected $message = ['name.require'=>'名称不能为空!',
'day.require'=>'天数不能为空!',
];




//软删除(delete_time,0)  'action'     => 'require|unique:AdminMenu,app^controller^action,delete_time,0',

//    protected $scene = [
//        'add'  => ['name', 'app', 'controller', 'action', 'parent_id'],
//        'edit' => ['name', 'app', 'controller', 'action', 'id', 'parent_id'],
//    ];


}
