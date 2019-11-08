<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;



/**
 * 注册帐户 Create             post
 * 工作单位 Units              get
 * 用户登录 userLogin          get
 * 个人信息 Information        get
 * 修改     Modify             put
 * 试题     Questions          get
 * 统计分数 Widget             post
 * 试题库  ItemBank            get
 * */

/**注册帐户          Create             post*/
Route::rule('port/Create','api/ApiSet/CreateAccount','post');
/** 工作单位         Units              get  */
Route::rule('port/Units','api/ApiSet/WorkUnits','get');
/** 用户登录         userLogin          post   */
Route::rule('port/userLogin','api/ApiSet/userLogin','post');
/**个人信息          Information         get   */
Route::rule('port/Information','api/ApiSet/Information','get');
/** 修改             Modify             put  */
Route::rule('port/Modify','api/ApiSet/Modify','put');
/** 试题             Questions          get  */
Route::rule('port/Questions','api/ApiSet/TestQuestions','get');
/** 统计分数         Widget             post */
Route::rule('port/Widget','api/ApiSet/Widget','post');
/**系统服务           Service            get   */
Route::rule('port/Service', 'api/ApiSet/SystemService','get');
/** 接口开放申请   application  get */
Route::rule('port/application','api/ApiSet/application','get');
/**试题库   ItemBank    get*/
Route::rule('port/ItemBank','api/ApiSet/ItemBank','get');
return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
