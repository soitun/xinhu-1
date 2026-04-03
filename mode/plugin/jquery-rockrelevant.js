/**
	edittable 选择相关插件
	caratename：chenxihu
	caratetime：2026-03-06 21:40:00
	email:qqqq2900@126.com
	homepage:www.rockoa.com
*/

(function ($) {
	if(typeof(rockrelevantdata)=='undefined')rockrelevantdata = {};
	
	function rockrelevant(cans){
		var me 		= this;
		var defv = js.applyIf(cans,{
			rand:'abcd'+js.getrand(),
			ctype:'',
			isedit:1,
			data:[]
		});
		for(var i in defv)this[i] = defv[i];
		
		
		this.clickstr = function(act,val){
			return 'rockrelevantdata.'+this.rand+'.'+act+'('+val+')';
		}
		
		this.init = function(){
			rockrelevantdata[this.rand]  	= this;
			if(this.ctype=='show'){
				if(this.data)for(var i=0;i<this.data.length;i++){
					var d = this.data[i];
					this.showlist(d.name,''+d.num+'|'+d.mid+'');
				}
			}else{
				$.selectdata({
					data:[],title:'请选择',
					url:'index.php?d=flow&a=relevantdata&m=flowopt&num='+this.sysmodenum+'&datastr='+jm.base64encode(this.datastr)+'',
					checked:false,
					searchajax:false,
					width:450,
					maxshow:50,
					onselect:function(seld,sna,sid){
						if(sid)me.showlist(sna,sid);
					}
				});
			}
		}
		
		this.showlist = function(sna,sid){
			var a   = sid.split('|');
			var slx = (ismobile == 0) ? 'p':'x';
			var url = 'task.php?a='+slx+'&num='+a[0]+'&mid='+a[1]+'';
			var s = '<div data-value="'+sid+'" class="list-items">'+sna+' <a href="javascript:;" onclick="js.open(\''+url+'\')">详</a>';
			if(this.isedit==1)s+=' <a href="javascript:;" onclick="'+this.clickstr('deletelist','this')+'">×</a>';
			s+='</div>';
			$('#relevantview_'+this.fname+'').append(s);
			this.showvalue();
		}
		
		this.showvalue = function(){
			var obj = $('#relevantview_'+this.fname+'').find('[data-value]');
			var ss = '',val;
			for(var i=0;i<obj.length;i++){
				val = $(obj[i]).attr('data-value');
				if(ss)ss+=',';
				ss+=val;
			}
			if(form(this.fname))form(this.fname).value = ss;
		}
		
		this.deletelist = function(o){
			$(o).parent().remove();
			this.showvalue();
		}
		
	}

	$.rockrelevant	= function(cans){
		var funcls = new rockrelevant(cans);
		funcls.init();
		return funcls;
	};
	
})(jQuery);