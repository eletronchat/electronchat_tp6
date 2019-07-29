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
      from: '/local/chat/index/input',  
      onMessage: function(e) {
        //接收聊天输入回车信号
        if (e.data.is_enter) {
          var content = layedit.getText(index);
          layedit.cleanContent(index);
        }
      }
    });
    post.sent({
      from: '/local/chat/index/input',
      data: {content: '你是入口文件吗？' },
      to: '/local/chat' // 接收人  
    });
    //客户列表区块消息接收地址并处理
    post.inbox({
        from: '/local/chat/index/message/onlineList',
        onMessage: function(e) {
            var $ = layui.jquery,
                element = layui.element,
                microtime = new Date(parseInt(e.microtime * 1000)),
                data = e.data;
            $('.guest-online-list').after(
                '<div class="layui-colla-content layui-show">'
                +    '<span class="chat-guest">'
                +    data.speaker
                +    '</span>'
                +    '<span class="layui-badge layui-bg-green chat-new-message">new</span'
                +    '<span>'
                +    '</span>'
                +    '<br>'
                +    '<span class="chat-message">'
                +        data.content
                +    '</span>'
                +    '<span class="chat-new-message-date">'
                +     microtime.getHours() + ':' + microtime.getMinutes() + ':' + microtime.getSeconds()
                +    '</span>'
                +'</div>'
            ); 
            $('#online-num').text($('.guest-online-list').siblings().length);
        }
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
