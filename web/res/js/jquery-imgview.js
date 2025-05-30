/**
*	图片查看
*	createname：雨中磐石
*	homeurl：http://www.rockoa.com/
*	Copyright (c) 2016 rainrock (xh829.com)
*	Date:2016-01-01
*/

(function ($) {
	
	function get(id){return document.getElementById(id)};
	
	function funclass(opts, obj){
		if(!opts)opts={};
		var me = this;
		this.ismobile = false;
		this.downbool = true;
		this.dushu	  = 0;
		this.onloadsuccess=function(){};
		this.init=function(){
			if(get('imgview_main'))return;
			for(var i in opts)this[i]=opts[i];
			if(obj)this.url=obj.attr('src');
			this.mheiht = document.body.scrollHeight,sed=$(window).height();
			if(this.mheiht<sed)this.mheiht=sed;
			this.showview();
		};
		this.remove=function(){
			$('#imgview_main').remove();
		};
		this.showview=function(){
			var s='<div id="imgview_main" style="position:absolute;left:0px;top:0px;width:100%;height:100%;z-index:800">';
			s+='<div style="position:absolute;z-index:0;left:0px;top:0px;width:100%;height:'+this.mheiht+'px;background-color:rgba(0,0,0,0.6)" id="imgview_mask"></div>';
			s+='<span onclick="$(this).parent().remove()" style="position:fixed;z-index:2;top:2px;right:5px;color:white"><i class="icon-remove"></i></span>';
			s+='<div id="imgview_span" style="position:fixed;z-index:1;left:47%;top:47%;overflow:hidden;color:white">';
			s+='	<div id="imgview_spanmask"  style="position:absolute;z-index:1;left:0px;top:0px;background-color:rgba(0,0,0,0);width:100%;height:100%;cursor:move;user-select:none;-moz-user-select:none;-webkit-user-select:none;-ms-user-select:none;-khtml-user-select:none;"></div>';
			s+='	<img style="position:absolute;z-index:0;left:0px;top:0px" id="imgview_spanimg" width="100%" height="100%" src="images/mloading.gif" >';
			s+='</div>';
			s+='<div style="position:fixed;z-index:2;left:0px;bottom:0px;text-align:center;color:white;width:100%;font-size:20px;background-color:rgba(0,0,0,0.2);height:40px;line-height:40px;overflow:hidden;user-select:none"><i style="cursor:pointer" id="imgview_zoom-out" class="icon-zoom-out" title="缩小"></i> &nbsp; <span id="imgview_nowbili" style="font-size:14px">100%</span> &nbsp; <i style="cursor:pointer" class="icon-zoom-in" title="放大" id="imgview_zoom-in"></i>';
			s+='  &nbsp; <i style="cursor:pointer" class="icon-move" title="原始大小" id="imgview_zoom-move"></i>';
			if(!this.ismobile && this.downbool)s+='  &nbsp; <a target="_blank" download="" style="color:white;font-size:20px" href="'+this.url+'"><i style="cursor:pointer" class="icon-download-alt" title="下载"></i></a>';
			s+='  &nbsp; <i style="cursor:pointer" class="icon-refresh" title="旋转90度" id="imgview_zoom-refresh"></i>';
			s+='</div>';
			s+='</div>';
			$('body').append(s);
			$('#imgview_mask').click(function(){
				$('#imgview_main').remove();
			});
			this.showez(32,32,0);
			var img = new Image();
			img.src = this.url;
			img.onload = function(){
				if(get('imgview_main')){
					me.showez(this.width,this.height, 1);
					try{
					$('#imgview_span').mousewheel(function(e){
						me.bilixxx(e.deltaY*0.1);
					});}catch(e){}
					me.rotate(me.dushu);
					me.initmove();
				}
				me.onloadsuccess(this);
			}
			img.onerror=function(e){
				$('#imgview_span').html('无图');
			}
			$('#imgview_zoom-out').click(function(){
				me.bilixxx(-0.1);
			});
			$('#imgview_zoom-in').click(function(){
				me.bilixxx(0.1);
			});
			$('#imgview_zoom-refresh').click(function(){
				me.clickrotate();
			});
			$('#imgview_zoom-move').click(function(){
				me.bl=1;
				me.rotate(0);
				me.bilixxx(0);
			});
		};
		this.showez=function(w, h, lx){
			this.width = w;
			this.height = h;
			var zw = $(window).width(),zh=$(window).height();
			var wm = zw-50,wh = zh-50;
			var bl= 1,nw=w,nh=h;
			if(w>wm){
				bl=wm/w;
				nh = h*bl;
			}
			if(nh>wh){
				bl= wh/h;
			}
			this.showbl(bl,lx);
		};
		this.showbl=function(bl,lx){
			this.bl = bl;
			$('#imgview_nowbili').html(''+parseInt(bl*100)+'%');
			var zw = $(window).width(),zh=$(window).height();
			var nw = this.width*this.bl,nh=this.height*this.bl;
			var l = (zw-nw)*0.5,t = (zh-nh)*0.5;
			var arr = {left:''+l+'px',top:''+t+'px',width:''+nw+'px',height:''+nh+'px'};
			var o1 = $('#imgview_span');
			if(lx!=2){
				if(lx==1)get('imgview_spanimg').src=this.url;
				o1.css(arr);
			}else{
				o1.stop();
				o1.animate(arr,300);
			}
		};
		this.bilixxx=function(lx){
			var bl = this.bl+lx;
			if(bl<0)bl=0.05;
			if(bl>3)bl=3;
			this.showbl(bl,2);
		};
		this.initmove=function(){
			if(this.ismobile){
				this.movehammer();
				return;
			}
			var o = $('#imgview_spanmask');
			var x=0,y=0,oldl,oldt;
			o.mousedown(function(e){
				x=e.clientX;
				y=e.clientY;
				var o1=get('imgview_span');
				oldl = parseInt(o1.style.left);
				oldt = parseInt(o1.style.top);
				me.movebo=true;
			});
			o.mousemove(function(e){
				if(!me.movebo)return;
				var _x = e.clientX-x,_y=e.clientY-y;
				$('#imgview_span').css({left:''+(oldl+_x)+'px',top:''+(oldt+_y)+'px'});
			});
			o.mouseup(function(e){
				me.movebo=false;
			});
		};
		this.rotate=function(ds){
			var o = get('imgview_span');
			var val= "rotate("+ds+"deg)";
			o.style.transform=val;
			o.style.webkitTransform=val;
			o.style.msTransform=val;
			o.style.MozTransform=val;
			o.style.OTransform=val;
		};
		this.clickrotate=function(){
			this.dushu+=90;
			if(this.dushu>=360)this.dushu=0;
			this.rotate(this.dushu);
		};
		this.movehammer=function(){
			var o = get('imgview_spanmask');
			var x=0,y=0,oldl,oldt;
			this.touchci = 0;
			o.addEventListener('touchstart',function(e){
				me.touchci++;
				x=e.touches[0].clientX;
				y=e.touches[0].clientY;
				var o1=get('imgview_span');
				oldl = parseInt(o1.style.left);
				oldt = parseInt(o1.style.top);
				me.movebo=true;
				clearTimeout(me.touctimes);
				me.touctimes = setTimeout(function(){me.touchci=0},200);
			}); 
			o.addEventListener('touchmove',function(e){
				e.preventDefault();
				if(!me.movebo)return;
				var _x = e.touches[0].clientX-x,_y=e.touches[0].clientY-y;
				$('#imgview_span').css({left:''+(oldl+_x)+'px',top:''+(oldt+_y)+'px'});
			}); 
			o.addEventListener('touchend',function(e){
				me.movebo=false;
				if(me.touchci==2){
					me.bilixxx(0.1);
					me.touchci=0;
				}
			}); 
		}
		
		this.loadimg=function(){
			return "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='UTF-8'%3F%3E%3Csvg width='80px' height='80px' viewBox='0 0 80 80' version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Ctitle%3Eloading%3C/title%3E%3Cdefs%3E%3ClinearGradient x1='94.0869141%25' y1='0%25' x2='94.0869141%25' y2='90.559082%25' id='linearGradient-1'%3E%3Cstop stop-color='%23606060' stop-opacity='0' offset='0%25'%3E%3C/stop%3E%3Cstop stop-color='%23606060' stop-opacity='0.3' offset='100%25'%3E%3C/stop%3E%3C/linearGradient%3E%3ClinearGradient x1='100%25' y1='8.67370605%25' x2='100%25' y2='90.6286621%25' id='linearGradient-2'%3E%3Cstop stop-color='%23606060' offset='0%25'%3E%3C/stop%3E%3Cstop stop-color='%23606060' stop-opacity='0.3' offset='100%25'%3E%3C/stop%3E%3C/linearGradient%3E%3C/defs%3E%3Cg stroke='none' stroke-width='1' fill='none' fill-rule='evenodd' opacity='0.9'%3E%3Cg%3E%3Cpath d='M40,0 C62.09139,0 80,17.90861 80,40 C80,62.09139 62.09139,80 40,80 L40,73 C58.2253967,73 73,58.2253967 73,40 C73,21.7746033 58.2253967,7 40,7 L40,0 Z' fill='url(%23linearGradient-1)'%3E%3C/path%3E%3Cpath d='M40,0 L40,7 C21.7746033,7 7,21.7746033 7,40 C7,58.2253967 21.7746033,73 40,73 L40,80 C17.90861,80 0,62.09139 0,40 C0,17.90861 17.90861,0 40,0 Z' fill='url(%23linearGradient-2)'%3E%3C/path%3E%3Ccircle id='Oval' fill='%23606060' cx='40.5' cy='3.5' r='3.5'%3E%3C/circle%3E%3C/g%3E%3CanimateTransform attributeName='transform' begin='0s' dur='1s' type='rotate' values='0 40 40;360 40 40' repeatCount='indefinite'/%3E%3C/g%3E%3C/svg%3E%0A";
		}
	}
	
	
	$.imgview	  = function(options){
		var cls = new funclass(options,false);
		cls.init();
		return cls;
	}
	
	$.fn.imgview	  = function(options){
		var cls = new funclass(options, $(this));
		cls.init();
		return cls;
	}
	
	$.imgviewclose= function(){
		var bo = get('imgview_main');
		$('#imgview_main').remove();
		return bo;
	}
	
})(jQuery); 