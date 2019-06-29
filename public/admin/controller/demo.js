/**
 *  只是一个demo模块
 *  @name   wuchuheng
 *  @date   2019/05/14
 *  @email  wuchuheng@163.com
 *  @blog   www.wuchuheng.com
 */

;layui.define(function(e) {
  layui.use(['layer', 'table', 'dtree', 'form'], function(){
    var layer = layui.layer,
      table = layui.table,
      dtree = layui.dtree,
      form  = layui.form,
      $ = layui.$;

        table.render({
        elem:'#table'
        ,headers: {"access_token": layui.data('layuiAdmin').access_token}
        ,url: layui.cache.rest_url + '/members'
        ,page: true                 //开启分页
        ,response:{ statusName:'errorCode' }
        ,cols:         [[                   //表头
        {type:'checkbox', fixed: 'left'}
        ,{field:        'uid',                title:     'ID',          width:60,   sort: true, align: 'center'}
        ,{field:       'account',           title:     '帐号',        width:100,align: 'center' }
        ,{field:       'username',          title:     '姓名',        width:90,align: 'center'}
        ,{field:       'img',               title:     '头像',        width:60,    templet: "#tableImg"}
        ,{field:       'phone',             title:     '<i class="layui-icon">&#xe642;</i>手机',        width:120,align: 'center'}
        ,{field:       'email',             title:     '邮箱',        width:150,align: 'center'}
        ,{field:       'receives',          title:     '接待量',      width:80, sort: true, align: 'center'}
        ,{field:       'nick_name',         title:     '昵称',        width:90,align: 'center'}
        ,{field:       'role',              title:     '角色',        width:      100,align: 'center'}
        ,{field:'is_lock', title:'是否锁定', width:110, templet: '#checkboxTpl', unresize: true,align: 'center'}
        ,{fixed: 'right', title:'操作', toolbar: '#table_bar', width:120,align: 'center'}
        ]]
        });
        //是否锁定事件 
        form.on('checkbox(is_lock)', function(obj){
          var is_lock = obj.elem.checked ? 1 : 0;
            $.ajax({
               headers: { "access_token": layui.data('layuiAdmin').access_token },
               url: layui.cache.rest_url+"/members/" + this.value,
               data: {is_lock:is_lock},
               type: "PUT",
               success: function(res) {
                   if (res.errorCode == 0) {
                     layer.msg(res.msg, {icon: 1});
                   } else {
                     layer.msg(res.msg, {icon: 2});
                   }
               },
               error: function(res){
                 layer.msg('响应失败', {icon: 5});
               }
            });
          
        });
        table.on('edit(table)', function(obj){ //注：edit是固定事件名，test是table原始容器的属性 lay-filter="对应的值"
          console.log(obj.value); //得到修改后的值
          console.log(obj.field); //当前编辑的字段名
          console.log(obj.data); //所在行的所有相关数据  
        });

      table.on('tool(test-table-demoEvent)', function(obj){
        var data = obj.data;
        if(obj.event === 'setSign'){
          layer.prompt({
            formType: 2
            ,title: '修改 ID 为 ['+ data.id +'] 的用户签名'
            ,value: data.sign
          }, function(value, index){
            layer.close(index);

            //这里一般是发送修改的Ajax请求

            //同步更新表格和缓存对应的值
            obj.update({
              sign: value
            });
          });
        }
      });




    //删除和修改
    layui.table.on('tool(table)', function(obj){
      if (obj.event === 'del') {
        layer.confirm('真的删除行么', function(index){
          layer.close(index);
          $.ajax({
            headers: {"access_token": layui.data('layuiAdmin').access_token},
            url: layui.cache.rest_url + "/members/" + obj.data.uid,
            type: "DELETE",
            success: function(res) {
               if (res.errorCode == 0) obj.del(); 
               else layer.msg(res.msg, {icon:2});
            },
            error: function() {
                layer.msg('请求失败', {icon:2});
            }
          }); 
        });
      }
    })




  });
  e("demo", {})
});

