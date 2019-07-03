/**
 *  角色业务处理模块
 *  @name   wuchuheng
 *  @date   2019/05/14
 *  @email  wuchuheng@163.com
 *  @blog   www.wuchuheng.com
 */


;layui.define(function(e) {
  //目录树
  layui.use(['admin', 'form', 'layer', 'table', 'dtree', 'upload'], function(){
    var layer = layui.layer,
        table = layui.table,
        dtree = layui.dtree,
        upload= layui.upload,
        $     = layui.$,
        form  = layui.form,
        admin = layui.admin,
        element = layui.element,
        router = layui.router();
  element.render();
     var DTree = dtree.render({
       headers: {
         "access-token": layui.data('layuiAdmin')['access-token']
       },
      elem: "#tree",
      url: layui.cache.rest_url+"/group",
      method: "get",
      icon: "2",
      initLevel: "1",
       line: true,
      toolbar: true,
      //skin: "zdy",
      toolbarStyle: {
        "title": "客服组"
     },
     done: function(data, obj){
       $("#search_btn").unbind("click");
       $("#search_node").click(function(){
         var value = $("#searchInput").val();
         if(value){
           var flag = DTree.searchNode(value); // 内置方法查找节点
           if (!flag) {layer.msg("该名称节点不存在！", {icon:5});}
         } else {
           DTree.menubarMethod().refreshTree(); // 内置方法刷新树
         }
       });
     },
      toolbarFun: {
        //增加节点
        addTreeNode: function(treeNode, $div){
          $.ajax({
            headers: {
              "access-token": layui.data('layuiAdmin')['access-token']
            },
            type: "post",
            data: treeNode,
            url: layui.cache.rest_url+"/group",
            success: function(result){
              console.log(result); 
              DTree.changeTreeNodeAdd("refresh"); // 添加成功，局部刷新树
            },
            error: function(result){
              DTree.changeTreeNodeAdd(false); // 添加失败
              layer.msg(result.msg);
            }
          });
        }, 
        //修改节点
        editTreeNode: function(treeNode, $div){
          $.ajax({
            headers: {
              "access-token": layui.data('layuiAdmin')['access-token']
            },
            type: "PUT",
            data: treeNode,
            url: layui.cache.rest_url+"/group",
            success: function(result){
              DTree.changeTreeNodeEdit(true);
            },
            error: function(){
              layer.msg(result.msg);
            }
          });
        },
        //删除节点
        delTreeNode: function(treeNode, $div){
          $.ajax({
            headers: {
              "access-token": layui.data('layuiAdmin')['access-token']
            },
            type: "DELETE",
            data: treeNode,
            url: layui.cache.rest_url+"/group",
            success: function(result){
              DTree.changeTreeNodeDel(true); // 删除成功
            },
            error: function(result){
              layer.msg(result.responseJSON.msg, {icon: 2});
            }
          });
        }
       },
       //iframe 加载

    });
    //dtree  单击事件
    // 绑定节点的单击事件
    dtree.on("node('tree')", function(obj){
        var id = obj.param.nodeId;
        table.reload("table", {url: "/api/v1/members?current_id=" + id});
    })
    form.render('select', 'search');//搜索表单渲染


    //****** 添加客服表单处理 s******//
    //头像上传
    var uploadInst = upload.render({
      elem: '#upload-img' //绑定元素
      ,url: '/upload/' //上传接口
      ,auto: false
      ,bindaction: "#submit-buttom"
      ,choose: function(obj){
        //头像预览
        obj.preview(function(index, file, result){
          $('img').attr('src', result);
        });
      }
      ,done: function(res){
        //上传完毕回调
      }
      ,error: function(){
        //请求异常回调
      }
    });
    //初始化角色值
    admin.req({
      url: layui.cache.rest_url + "/roleList",
      type: 'GET',
      done: function(res){
        for(var i in res.data) {
          var html = "<option value='"+res.data[i].id+"'>"+res.data[i].title+"</option>";
          $('select[name=group_id]').append(html);
        }
        form.render('select', 'add-member-form');//添加客服表单select渲染
      }
    });
    //客服分组选择回调
    form.on('select(select-group)', function(data){
      console.log(data);
    });
    layer.ready(function(){
      var DTree = dtree.render({
       headers: {
         "access-token": layui.data('layuiAdmin')['access-token']
       },
        elem: "#slTree",
        method: 'get',
        url: layui.cache.rest_url+"/group?addMember=1",
        icon: "2",
        accordion:true
      });
      $("#city").on("click",function(){
        $(this).toggleClass("layui-form-selected");
        $("#test").toggleClass("layui-show layui-anim layui-anim-upbit");
        $(".isshow").toggle();
      });
      dtree.on("node(slTree)", function(obj){
        layui.cache.select_node_id = obj.param.nodeId;
        $("#input_city").val(obj.param.context);
        $("#city").toggleClass("layui-form-selected");
        $("#test").toggleClass("layui-show layui-anim layui-anim-upbit");
      });
    });
    //验证
    form.verify({
      account: function(value){
        if(value.length < 5){
          return '帐号字符不少于6个字符';
        }
      }
      ,passwd: [
        /^[\S]{6,12}$/
        ,'密码必须6到12位，且不能出现空格'
      ]
      ,repasswd: function (value){
         if($('input[name=passwd]').val() !== value) {
             return '2次密码输入不一样';
         }
      }
      ,username: function(value){
        if(value.length < 2){
          return '请输入全名';
        }
      }
      ,receives: function(value) {
        if (value.length > 0) {
           if (!$.isNumeric(value)) {
             return '接待量必需是整数';
           }else if(parseInt(value) !== parseFloat(value)) {
             console.log(value);
             return '接待量必需是整数';
           }
        } 

      }
      ,phone: function(value){
        if(!value.match(/^1[3-9][0-9]\d{8}$/)){
          console.log(value);
          return '请输入11位手机号码';
        }
      } 
      ,select_role: function(value){
          if(value === '') {
              return '请选择权限角色';
          }
          
      }
    });
    //提交
    form.on('submit(add-member-form)', function(data){
      var memberData = data.field;
      if ( memberData.file !== '' ) 
         memberData.file = $('#preview').attr('src');
         delete memberData.group;
         memberData.member_group_id = layui.cache.select_node_id;
         $.ajax({
           headers: {
             "access-token": layui.data('layuiAdmin')['access-token']
           },
           url: layui.cache.rest_url + "/members",
           type: 'POST',
           data: memberData,
           success: function(res){
             layer.msg(res.msg, {icon:1});
             layer.close(layui.cache.addmember); //关闭表单
             $('#addmember-dom')[0].reset(); //重置表单
             //:xxx 重载左边目录树
             //重载数据表
            table.reload("table");
           },
           error: function(res){
                layer.msg(res.responseJSON.msg,  {icon: 5});
           }
    });
      return false;
    });
    //************ 添加客服表单 e ***********//

        //************ 数据表格,            start      *********//
        table.render({
        elem:'#table'
        ,headers: {"access-token": layui.data('layuiAdmin')['access-token']
        }
        ,url: layui.cache.rest_url + '/members'
        ,page: true                 //开启分页
        ,toolbar: '#test-table-toolbar-toolbarDemo'
        ,response:{ statusName:'errorCode' }
        ,cols:         [[                   //表头
        {type:'checkbox', fixed: 'left'}
        ,{field:        'uid',                title:     'ID',          width:60,   sort: true, align: 'center'}
        ,{field:       'account',           title:     '帐号',        width:120,align: 'center' }
        ,{field:       'username',          title:     '姓名',  width:90,align: 'center', edit:'text'}
        ,{field:       'img',               title:     '头像',        width:60,    templet: "#tableImg", event:'show_img'}
        ,{field:       'phone',             title:     '手机', edit:'text',        width:120,align: 'center'}
        ,{field:       'email',             title:     '邮箱',        width:"12%",align: 'center', edit:'text'}
        ,{field:       'receives',          title:     '接待量', edit:'text',      width: 80, sort: true, align: 'center'}
        ,{field:       'nick_name',         title:     '昵称',       align: 'center', edit:'text'}
        ,{field:       'role',              title:     '角色',        width:      100,align: 'center'}
        ,{field:'is_lock', title:'是否锁定', width:110, templet: '#checkboxTpl', unresize: true,align: 'center'}
        ,{fixed: 'right', title:'操作', toolbar: '#table_bar', width:120,align: 'center'}
        ]]
        });
        //是否锁定事件 
        form.on('checkbox(is_lock)', function(obj){
          var is_lock = obj.elem.checked ? 1 : 0;
            $.ajax({
               headers: { "access-token": layui.data('layuiAdmin') ['access-token']
               },
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

    //监听单元格编辑
    table.on('edit(table)', function(obj){
      var value = obj.value //得到修改后的值
        ,data = obj.data //得到所在行所有键值
        ,field = obj.field; //得到字段
        //:xxx 字段值要进行验证
      $.ajax({
        headers: { "access-token": layui.data('layuiAdmin') ['access-token']
        },
        url: layui.cache.rest_url+"/members/" + data.uid,
        data:{[field]:value},
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
     // layer.msg('[ID: '+ data.id +'] ' + field + ' 字段更改为：'+ value, {
     //   offset: '15px'
     // });
    });

    
    //删除和修改行
    layui.table.on('tool(table)', function(obj){
      if (obj.event === 'del_one') {
        layer.confirm('真的删除行么', function(index){
          layer.close(index);
          $.ajax({
            headers: {"access-token": layui.data('layuiAdmin')['access-token']
            },
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
      //显示头像
      if (obj.event === 'show_img') {
        layer.open({
          type: 1, 
          shade: 0.3,
          title: false,
          shadeClose: true,
          content: "<img src='"+obj.data.img+"' />"
        }); 
      }
      if (obj.event === 'edit') {
       layui.cache.addmember = admin.popup({
        title: '添加客服'
        ,shade: 0
        ,anim: -1
        ,area: ['700px', '600px']
        ,id: 'layadmin-layer-skin-test'
        ,skin: 'layui-anim layui-anim-upbit'
        ,content: $('#eidt-member-dom')
        ,btnAlign: 'c'
        ,scrollbar: false
        ,tips: [1, '#c00']
      });
      }
    })
    //监听工具栏事件
    //监听事件
    table.on('toolbar(table)', function(obj){
      var checkStatus = table.checkStatus(obj.config.id);
      switch(obj.event){
        case 'add':
          layui.cache.addmember = admin.popup({
            title: '添加客服'
            ,shade: 0
            ,anim: -1
            ,area: ['700px', '600px']
            ,id: 'layadmin-layer-skin-test'
            ,skin: 'layui-anim layui-anim-upbit'
            ,content: $('#addmember-dom')
            ,btnAlign: 'c'
            ,scrollbar: false
            ,tips: [1, '#c00']
          });
          break;
        case 'del':
          layer.msg('删除');
          break;
        case 'lock':
          layer.msg('锁定');
          break;
      };
    });

    //************ 数据表格,            end        **********//
    //************ 搜索, start ******************************/
      $.ajax({
        url: layui.cache.rest_url + "/roleList",
        headers: { "access-token": layui.data('layuiAdmin') ['access-token']
        },
        type: 'GET',
        success: function(res) {
            if (res.errorCode == 0) {
                for(i in res.data) {
                   $('select[name=top]').append("<option value='"+res.data[i].id+"'>"+res.data[i].title+"</option>");
                }
               form.render('select'); 
            }
        },
        error: function(res) {
            console.log(res);
        }
      });
    //************ 搜索, end ******************************/

    //************ 角色设置, start ***********************/
    table.render({
        elem:'#role_list'
        ,headers: {"access-token": layui.data('layuiAdmin')['access-token']
        }
        ,url: layui.cache.rest_url + '/roleList'
        ,page: true                 //开启分页
        ,response:{ statusName:'errorCode' }
        ,cols: [[
        {field:'title',title:'名称',width:100,align: 'center',fixed: 'left'}
        ,{field:'descript',title:'描述',  width:490,edit:'text'}
        ,{fixed: 'right', title:'操作', toolbar: '#role_bar', width:120,align: 'center'}
        ]]
        });
    layui.table.on('tool(role_list)', function(obj){
      if (obj.event === 'role_edit'){
        layui.cache.role_id = obj.data.id; 
        layui.cache.popuRight = admin.popupRight({
          id: 'LAY_adminPopupLayerTest'
          ,area: ['500px']
          ,success: function(){
            layui.view(this.id).render('system/role_tree')
          }
        });
      }
    });
    //************ 角色设置, end   ***********************/

  });
  
 //样式 
  layui.use('jquery', function(){
    var $ = layui.jquery;
    $('#tree').css("height", $(window).height()*0.65+"px");
    $('.layadmin-iframe').css("height", $(window).height()*0.81+"px");
 });
  //输入接口
  e("role", {});
});
