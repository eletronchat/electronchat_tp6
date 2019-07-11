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
      onMessage: function(e) {
        //接收聊天输入回车信号
        if (e.data.is_enter) {
            var content = layedit.getText(index);
           layedit.cleanContent(index);
    post.sent({
      data: {content: '编辑的内容是' + content +'。现转发给入口文件' },
      to: '/brower/chat/index' // 接收人  
    });
        }
      }
    });
    post.sent({
      data: {content: '你是入口文件吗？' },
      to: '/brower/chat/index' // 接收人  
    });
        
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
