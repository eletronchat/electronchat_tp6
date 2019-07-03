/**
 *  设置中心业务处理模块
 *  @name   wuchuheng
 *  @date   2019/07/3
 *  @email  wuchuheng@163.com
 *  @blog   www.wuchuheng.com
 */


;layui.define(["tree", "element"], function(e) {
     var tree = layui.tree,
         element = layui.element;
     // 二级菜单
     element.render('nav', 'sub-menu');

    var inst1 = tree.render({
      elem: '#sub-tree'  //绑定元素
      ,skin: 'sidebar'
      ,data: [{
        title: '江西' //一级菜单
        ,children: [{
          title: '南昌' //二级菜单
          ,children: [{
            title: '高新区' //三级菜单
            //…… //以此类推，可无限层级
          }]
        }]
      },{
        title: '陕西' //一级菜单
        ,children: [{
          title: '西安' //二级菜单
        }]
      }]
    });


     e("setting", {})
});
