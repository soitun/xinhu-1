/**
	edittable 单击选择插件
	caratename：chenxihu
	caratetime：214-04-06 21:40:00
	email:qqqq2900@126.com
	homepage:www.xh829.com
*/

(function ($) {
	rockselectdata = {};
	function rockselect(cans){
		var me 		= this;
		var defv = js.applyIf(cans,{
			rand:js.getrand(),
			limit:10,
			maxheight:400,
			num:''
		});
		for(var i in defv)this[i] = defv[i];
		
		this.init = function(){
			if(!this.num)this.num 	 	= this.rand;
			if(!rockselectdata[this.num])rockselectdata[this.num] = {};
			this.showView();
			if(rockselectdata[this.num].alldata){
				this.loaddatashow(rockselectdata[this.num].alldata);
			}else{
				this.loaddata();
			}
			rockselectdata[this.num]  	= this;
		}
		
		this.clickstr = function(act,val){
			return 'rockselectdata.'+this.num+'.'+act+'('+val+')';
		}
	
		
		this.showView = function(){
			this.hide();
			var o2    = $(this.viewobj);
			var lefta =o2.offset();
			this.top  = lefta.top+o2.height();
			var s = '<div id="rockselectdiv" class="box" style="position:absolute;z-index:999;left:'+lefta.left+'px;top:'+this.top+'px;background:white;border:1px var(--main-color) solid;border-radius:5px;"><div style="background:var(--main-bgcolor);border-radius:5px">';
			s+='<div style="display:flex;border-bottom:var(--border)"><select style="width:100px;border:none;background:none;display:none"  id="rockselect_select"><option value="">-选择-</option></select><input type="input" style="background:none;border:none;border-radius:0;;flex:1" placeholder="输入关键词搜索" onkeydown="'+this.clickstr('keydown','this')+'" class="input"></div>';
			s+='<div id="rockselectdivs" style="max-height:'+this.maxheight+'px;overflow:auto"><div style="padding:50px;" align="center">'+js.ling(30)+'</div></div>';
			s+='</div></div>';
			$('body').append(s);
		}
		
		this.loaddata = function(key){
			if(!key)key='';if(!this.url)return;
			$.ajax({
				type:'get',data:{key:jm.base64encode(key)},
				url:this.url,dataType:'json',
				success:function(ret){
					me.loaddatashow(ret);
				},
				error:function(){
					$('#rockselectdivs').html('加载错误');
				}
			});
		}
		
		this.loaddatashow=function(ret){
			this.alldata = ret;
			var rows = ret;
			if(ret.rows)rows = ret.rows;
			if(ret.data)rows = ret.data;
			if(this.ondatachuli)rows = this.ondatachuli(rows, ret);
			this.yuandata = rows;
			this.firstdata(rows);
		}
		this.pageload=function(zl,p){
			$('#rockselectdivpage').remove();
			var ds = this.autodata;
			var str='',i,len=ds.length,j=0,sty,d,cls,str1='';
			for(i=(p-1)*zl;i<len;i++){
				d = ds[i];sty= '';cls='list-itemv';
				if(d.style)sty+=''+d.style+';';
				if(d.padding)sty+='padding-left:'+d.padding+'px;';
				if(d.disabled)cls='';
				str+='<div class="'+cls+'"';
				if(!d.disabled)str+=' onclick="'+this.clickstr('itemclick',''+i+'')+'"';
				str+= ' style="padding:7px 10px;'+sty+'">'+d.name+'';
				if(d.subname)str+='&nbsp;<span style="font-size:12px">('+d.subname+')</span>';
				str+='</div>';
				j++;
				if(j>=zl)break;
			}
			if(len>zl){
				str1='<div id="rockselectdivpage" style="padding:8px 10px;background:rgba(0,0,0,0.1)">总记录'+len+'条('+Math.ceil(len/zl)+'/'+p+')';
				if(p>1)str1+='&nbsp;<span class="zhu cursor" onclick="'+this.clickstr('pageload',''+zl+','+(p-1)+'')+'">&lt;上页</span>';
				if(j==zl && ds[p*zl])str1+='&nbsp;<span class="zhu cursor" onclick="'+this.clickstr('pageload',''+zl+','+(p+1)+'')+'">下页&gt;</span>';
				str1+='</div>';
			}
			if(!str)str='<div align="center" style="padding:30px">无记录</div>';
			setTimeout(function(){
				$('#rockselectdivs').html(str).after(str1);
				if(p==1)me.setweizhi();
			},10);
		}
		
		//点击
		this.itemclick = function(i){
			var d = this.autodata[i];
			if(d.disabled)return;
			var nav = d.name;
			if(this.nameobj)this.nameobj.value = nav;
			var val = d.value;
			if(typeof(val)=='undefined')val = d.id;
			if(typeof(val)=='undefined')val = d.name;
			if(this.idobj)this.idobj.value = val;
			if(this.onitemclick)this.onitemclick(nav,val,d);
			this.hide();
		}
		
		this.keydown = function(o1){
			if(!this.yuandata)return;
			clearTimeout(this.autoctime);
			this.autoctime = setTimeout(function(){me.sousouval(o1);},10);
		}
		
		this.sousouval = function(o1){
			var ds=[],val= strreplace(o1.value);
			var da = this.yuandata,len=da.length,j=0,zl=this.limit;
			if(val){
				for(i=0;i<len;i++)if(da[i].name.indexOf(val)>-1 || (da[i].subname && da[i].subname.indexOf(val)>-1)){
					ds.push(da[i]);j++;if(j>=zl*3)break;
				}
			}else{
				ds=da;
			}
			this.firstdata(ds);
			this.nowinpvle= val;
		}
		
		this.firstdata = function(ds){
			this.autodata = ds;
			this.pageload(this.limit,1);
		}
		
		this.hide = function(){
			$('#rockselectdiv').remove();
		}
		
		//设置位置
		this.setweizhi = function(){
			var obj = $('#rockselectdiv');
			var hei = obj.height() + this.top;
			var khe = winHb() + $(document).scrollTop();
			var dhe = hei - khe,min=200;
			if(dhe > 0){
				var o2 = $('#rockselectdivs');
				var nhei= o2.height()-dhe-5;
				if(nhei < min){
					var ntop = this.top - (min-nhei);
					if(ntop < 0){
						min  = min + ntop - 5;
						ntop = 5;
					}
					obj.css('top',''+ntop+'px');
					nhei = min;
				}
				o2.css('height',''+nhei+'px');
			}
		}
		
		
		this.setSelectData = function(dt,na, fid){
			if(!dt || dt.length==0)return;
			var o = get('rockselect_select');
			o.length = 0;
			$(o).show();
			dt.unshift({value:'',name:na});
			js.setselectdata(o, dt, 'value');
			$(o).change(function(){
				me.changeselect(this, fid);
			});
		}
		this.changeselect = function(o, fid){
			var val = o.value;
			var da  = this.yuandata,len=da.length,ds=[],i;
			if(val){
				for(i=0;i<len;i++)if(val==da[i][fid])ds.push(da[i]);
			}else{
				ds=da;
			}
			this.firstdata(ds);
		}
		
	}
	js.addbody('rockselectdiv', 'remove','rockselectdiv');
	$.rockselect	= function(cans){
		var funcls = new rockselect(cans);
		setTimeout(function(){funcls.init()},5);
		return funcls;
	};
	
})(jQuery);