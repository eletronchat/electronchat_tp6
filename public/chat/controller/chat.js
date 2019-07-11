/**
 *  layout  会话管理模块  
 *  @name   wuchuheng
 *  @date   2019/07/05
 *  @email  wuchuheng@163.com
 *  @blog   www.wuchuheng.com
 */
;layui.define(function(e) {
   
  layui.use(['element', 'jquery', 'form', 'layer', 'layedit', 'post'], function(){
    var layer = layui.layer,
        form  = layui.form,
        post  = layui.post,
        $     = layui.jquery,
        element = layui.element;
        element.render();
        element.render('collapse');
        layedit = layui.layedit;
    var index = layedit.build('input-area', {
     height : 180,
     tool: [
       'face' //表情
       ,'image' //插入图片
       ,'strong' //加粗
       ,'link' //超链接
       ,'help' //帮助
     ]
    }); 
    post.inbox({
      from: '/brower/chat/index/message',  
      onMessage: function(data) {
        //接收聊天输入回车信号
        console.log(data.value);
        if (data.value.is_enter) {
            console.log(layedit.getText(index));
        }
      }
    });
    //post.sent({
    //  data: {
    //    value: '我是chat页面', // 发件的内容
    //    to: 'websocket' // 接收人  
    //  }
    //});
        
    form.render(); 
    layedit.sync(index); 
    layer.ready(function(){
      var objDiv = document.getElementById('chat-log')
      objDiv.scrollTop = objDiv.scrollHeight;
    });
  });
  
  //输入接口
  e("chat", {});
});
