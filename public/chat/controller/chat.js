/**
 *  layout  会话管理模块  
 *  @name   wuchuheng
 *  @date   2019/07/05
 *  @email  wuchuheng@163.com
 *  @blog   www.wuchuheng.com
 */
;layui.define(function(e) {
   
  layui.use(['element', 'form', 'layer', 'layedit'], function(){
    var layer = layui.layer,
        form  = layui.form,
        element = layui.element;
        element.render();
        element.render('collapse');
        layedit = layui.layedit;
    layedit.build('input-area', {
     height : 180,
     tool: [
       'face' //表情
       ,'image' //插入图片
       ,'strong' //加粗
       ,'link' //超链接
       ,'help' //帮助
     ]
    }); 
    form.render(); 
  });
  //输入接口
  e("chat", {});
});
