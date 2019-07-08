/*
* @Author: wuchuheng
* @Date:   2019/07/08
* @Version: v0.0.1
* @email   wuchuheng@163.com
*/

layui.define(['jquery', 'form'], function(exports){

	var obj = {
    inbox: function(obj){
      this.from = obj.from;
      obj.from = obj.from; 
      var work = new SharedWorker('./work/worker.js', 'work'),
        worker = work.port;
      worker.start();
      this.worker = worker;
      worker.addEventListener('message', function(e) {
        //如果消息是给这个用户的，就回调
          //obj.onMessage(e.data);
        if (typeof e.data.to !== 'undefined' && e.data.to === obj.from)  {
          obj.onMessage(e.data);
        }
      });

      //在共享线程注册
      worker.postMessage({
        status: 0,
      }); 
    },
    sent: function(obj) {
      obj.data.from = this.from; 
      obj.data.time = typeof obj.data.time === 'undefined' ? (new Date()).valueOf() : obj.data.time;
      var worker = this.worker;
      worker.postMessage({
        status: 2,
        data: obj.data 
      });
    }
	};
	exports('post', obj);
});
