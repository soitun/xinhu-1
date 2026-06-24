/**
	rockqueue 队列插件
	caratename：chenxihu
	caratetime：2104-04-06 21:40:00
	email:qqqq2900@126.com
	homepage:www.rockoa.com
*/

(function ($) {
	function rockqueue(cans){
		var me 		= this;
		var defv = js.applyIf(cans,{
			rand:js.getrand(),
			successall:function(){},
			successone:function(){},
			tishi:'处理',
			waiting:function(){}
		});
		for(var i in defv)this[i] = defv[i];
		
		this.init = function(){
			this.cg = 0;
			this.sb = 0;
			this.len= this.data.length;
			this.startbool=true;
			this.msgstr 	= '';
			js.loading(''+this.tishi+'中('+this.len+'/<span id="queuecishu'+this.rand+'">1</span>)...');
			this.start(0);
		}
		
		this.wanchen = function(){
			setTimeout(function(){me.successall();},2000);
			var str = ''+this.tishi+'完成，总<b>'+this.len+'</b>条，成功'+this.cg+'次，失败'+this.sb+'次';
			if(this.msgstr)str+='<br>'+this.msgstr+'';
			js.msgok(str);
		}
		
		this.start = function(xu){
			if(xu >= this.len || !this.startbool){
				this.wanchen();
				return;
			}
			var d = this.data[xu];var type = d.type;if(!type)type='get';
			$('#queuecishu'+this.rand+'').html(''+(xu+1)+'');
			this.waiting(d);
			$.ajax({
				url:d.url,
				type:type,
				success:function(bstr){
					me.cg++;
					me.successone(d, bstr, true);
					me.start(xu+1);
				},
				error:function(e){
					me.sb++;
					me.successone(d, '出错:'+e.responseText+'', false);
					me.start(xu+1);
				}
			});
		}
		
		this.stop = function(str){
			this.startbool = false;
			if(str)this.setmsg(str);
		}
		
		this.setmsg = function(str){
			this.msgstr = str;
		}
	}
	$.rockqueue	= function(cans){
		var funcls = new rockqueue(cans);
		funcls.init();
		return funcls;
	};
	
})(jQuery);