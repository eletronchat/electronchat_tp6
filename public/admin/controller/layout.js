/**
 *  layout 模板布局切换
 *  @name   wuchuheng
 *  @date   2019/07/01
 *  @email  wuchuheng@163.com
 *  @blog   www.wuchuheng.com
 */
;layui.define(function(e) {
   
  layui.use(['laytpl', 'admin', 'form', 'layer', 'table', 'dtree', 'upload'], function(){
    var layer = layui.layer,
       table = layui.table,
        dtree = layui.dtree,
        upload= layui.upload,
        $     = layui.$,
        form  = layui.form,
        admin = layui.admin,
        element = layui.element,
        router = layui.router(),
        laytpl = layui.laytpl;
    $("#userinfo").attr('lay-url', '/hello');
  });
  //输入接口
  e("layout", {});
});
