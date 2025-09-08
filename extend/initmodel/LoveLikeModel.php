<?php

namespace initmodel;

/**
 * @AdminModel(
 *     "name"             =>"LoveLike",
 *     "name_underline"   =>"love_like",
 *     "table_name"       =>"love_like",
 *     "model_name"       =>"LoveLikeModel",
 *     "remark"           =>"点赞&amp;收藏",
 *     "author"           =>"",
 *     "create_time"      =>"2024-08-24 09:38:05",
 *     "version"          =>"1.0",
 *     "use"              => new \initmodel\LoveLikeModel();
 * )
 */


use think\facade\Db;
use think\Model;
use think\model\concern\SoftDelete;


class LoveLikeModel extends Model
{

    protected $name = 'love_like';//点赞&amp;收藏

    //软删除
    protected $hidden            = ['delete_time'];
    protected $deleteTime        = 'delete_time';
    protected $defaultSoftDelete = 0;
    use SoftDelete;
}
