<?php 

use think\facade\Route;

//Route::get(':version/menu', ':version.Index/index');


//api 后台模式菜单
Route::group(':version', function(){
  //自动切换模式
  Route::get('/menu', ':version.Menu/list');
  //管理模式和客服模式选择
  Route::get('/menu/:mode',  ':version.Menu/list');
});

//api 客服管理
Route::Group(':version', function(){
  //客服分类目录树
  Route::get('/group', ':version.Role/getAllGroup');
  //客服分类目录树
  Route::post('/group', ':version.Role/addNode');
  //修改节点
  Route::put('/group', ':version.Role/editNode');
  //删除节点
  Route::delete('/group', ':version.Role/delNode');
});

//权限管理
Route::Group(':version', function(){
  //所有权限角色列表
  Route::get('/roleList', ':version.Role/getRoleList');
  //单个角色权限目录树
  Route::get('/roleList/:id', ':version.Role/getRoleById');
  //更新角色
  Route::put('/roleList/:id', ':version.Role/uploadRoleById');
});

//成员管理
Route::Group(':version', function(){
  //添加成员
  Route::post('/members', ':version.Role/addMember');
  //读取成员
  Route::get('/members', ':version.Role/getMembers');
  //修改成员
  Route::put('/members/:uid', ':version.Role/editMember');
  //删除成员
  Route::delete('/members/:uid', ':version.Role/del');
});
//获取token
Route::Group(':version', function(){
  //获取token
  Route::get('/token', ':version.Token/getToken');
  //获取验证码
  //Route::get('/verCode/:time', ':version.Token/getVerCode');
  //登出
  Route::put('/logout', ':version.Token/logout');
});

//上传
Route::post(':version/upload', ':version.');

return [

];
