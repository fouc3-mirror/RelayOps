<?php
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
use think\facade\Route;

Route::group('install', function () {
    Route::get('/', '\app\install\controller\Install::index');
    Route::get('step2', '\app\install\controller\Install::step2');
    Route::get('step3', '\app\install\controller\Install::step3');
    Route::get('step4', '\app\install\controller\Install::step4');
    Route::get('step5', '\app\install\controller\Install::step5');
    Route::get('complete', '\app\install\controller\Install::complete');
    Route::post('testDb', '\app\install\controller\Install::testDb');
    Route::post('install', '\app\install\controller\Install::install');
})->middleware(\app\install\middleware\CheckInstall::class);

Route::group('api', function () {
    Route::post('user/login', 'api.User/login');
    Route::post('user/register', 'api.User/register');
    Route::post('user/verify', 'api.User/sendVerify');
    Route::post('admin/login', 'api.Admin/login');
    Route::post('user/reset-password', 'api.User/resetPassword');
    Route::post('admin/send-reset-verify', 'api.Admin/sendResetVerify');
    Route::post('admin/reset-password', 'api.Admin/resetPassword');
    Route::get('user/products', 'api.User/products');
    Route::get('user/product/:id', 'api.User/productDetail');
    Route::post('pay/notify', 'api.Pay/notify');
    Route::get('pay/return', 'api.Pay/returnPage');
})->middleware(\app\middleware\Cors::class);

Route::group('api/node', function () {
    Route::get('user', 'api.NodeController/user');
    Route::get('can-create-proxy', 'api.NodeController/canCreateProxy');
    Route::post('traffic', 'api.NodeController/traffic');
    Route::post('heartbeat', 'api.NodeController/heartbeat');
})->middleware(\app\middleware\NodeAuth::class);

Route::group('api/user', function () {
    Route::get('info', 'api.User/info');
    Route::get('logout', 'api.User/logout');
    Route::post('logout', 'api.User/logout');
    Route::get('nodes', 'api.User/nodes');
    Route::get('ports', 'api.User/ports');
    Route::post('cart/add', 'api.User/cartAdd');
    Route::get('cart', 'api.User/cartList');
    Route::post('cart/remove', 'api.User/cartRemove');
    Route::post('cart/clear', 'api.User/cartClear');
    Route::post('order/create', 'api.User/orderCreate');
    Route::post('order/create-direct', 'api.User/orderCreateDirect');
    Route::get('order/:id/pay', 'api.User/orderPay');
    Route::get('order/:id', 'api.User/orderDetail');
    Route::get('orders', 'api.User/orderList');
    Route::get('client/detail', 'api.User/clientDetail');
})->middleware(\app\middleware\Cors::class)
  ->middleware(\app\middleware\UserAuth::class);

Route::group('api/admin', function () {
    Route::get('info', 'api.Admin/info');
    Route::post('logout', 'api.Admin/logout');
    Route::get('nodes', 'api.Admin/nodeList');
    Route::get('node/detail', 'api.Admin/nodeDetail');
    Route::post('node/save', 'api.Admin/nodeSave');
    Route::post('node/delete', 'api.Admin/nodeDelete');
    Route::post('node/toggle', 'api.Admin/nodeToggle');
    Route::get('users', 'api.Admin/userList');
    Route::get('user/detail', 'api.Admin/userDetail');
    Route::post('user/save', 'api.Admin/userSave');
    Route::post('user/delete', 'api.Admin/userDelete');
    Route::post('user/toggle', 'api.Admin/userToggle');
    Route::get('settings', 'api.Admin/settings');
    Route::post('settings', 'api.Admin/saveSettings');
    Route::post('test-email', 'api.Admin/testEmail');
    Route::post('test-pay', 'api.Admin/testPay');
    Route::get('products', 'api.Admin/productList');
    Route::post('product/save', 'api.Admin/productSave');
    Route::post('product/delete', 'api.Admin/productDelete');
    Route::post('product/toggle', 'api.Admin/productToggle');
    Route::get('orders', 'api.Admin/orderList');
    Route::post('order/save', 'api.Admin/orderSave');
    Route::post('order/pay', 'api.Admin/orderPay');
    Route::post('order/delete', 'api.Admin/orderDelete');
})->middleware(\app\middleware\Cors::class)
  ->middleware(\app\middleware\AdminAuth::class);

Route::get('/', 'Index/index');
Route::get('login', 'Index/login');
Route::get('about', 'Index/about');
Route::get('console/shop', 'Index/shop')->middleware(\app\middleware\UserAuth::class);
Route::get('console/client/:id', 'Index/clientDetail')->middleware(\app\middleware\UserAuth::class);
Route::get('console', 'Index/console')->middleware(\app\middleware\UserAuth::class);
Route::get('product/:id', 'Index/product')->middleware(\app\middleware\UserAuth::class);

Route::get('admin/login', 'Admin/login');

Route::group('admin', function () {
    Route::get('/', 'Admin/index');
    Route::get('dashboard', 'Admin/dashboard');
    Route::get('nodes', 'Admin/nodes');
    Route::get('products', 'Admin/products');
    Route::get('users', 'Admin/users');
    Route::get('orders', 'Admin/orders')->middleware(\app\middleware\AdminAuth::class);
})->middleware(\app\middleware\AdminAuth::class);

Route::get('migrate', 'Migrate/index');
Route::get('migrate/preview', 'Migrate/preview');
Route::post('migrate/execute', 'Migrate/execute');
Route::get('think', fn() => 'hello,ThinkPHP8!');

Route::get('hello/:name', 'index/hello');
