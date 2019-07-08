/**
 *  layout  会话管理模块  
 *  @name   wuchuheng
 *  @date   2019/07/05
 *  @email  wuchuheng@163.com
 *  @blog   www.wuchuheng.com
 */
;layui.define(function(e) {
   
  layui.use(['element', 'form', 'layer', 'layedit', 'post'], function(){
    var layer = layui.layer,
        form  = layui.form,
        post  = layui.post,
        element = layui.element;
        element.render();
        element.render('collapse');
        layedit = layui.layedit;
        post.inbox({
          from: 'chat',  
          onMessage: function(data) {
            console.log(data); 
          }
        });

        post.sent({
          data: {
            value: '我是chat页面', // 发件的内容
            to: 'websocket' // 接收人  
          }
        });
        
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
