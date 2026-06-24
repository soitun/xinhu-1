<?php
/**
*	模块：finorder.订单中心
*	说明：自定义区域内可写你想要的代码
*	来源：流程模块→表单元素管理→[模块.订单中心]→生成列表页
*/
defined('HOST') or die ('not access');
?>
<script>
$(document).ready(function(){
	{params}
	var modenum = 'finorder',modename='订单中心',isflow=0,modeid='164',atype = params.atype,pnum=params.pnum,modenames='',listname='Zmlub3JkZXI:';
	if(!atype)atype='';if(!pnum)pnum='';
	var fieldsarr = [],fieldsselarr= [],chufarr= [];
	
	<?php
	include_once('webmain/flow/page/rock_page.php');
	?>
	
//[自定义区域start]
if(pnum=='all'){
	var zhangid = '';
	bootparams.checked = true;//开启多选
	c.addrightbtn(c.getbtnstr('确认收款','queren','success'));
	c.addrightbtn(c.getbtnstr('设置默认收款的帐号','cogfin'));
	c.addrightbtn(c.getbtnstr('生成记账单','creagejizhang'));
	c.queren = function(){
		if(atype!='alldaisk'){
			js.msgerror('请切换到“支付待确认”的列表下');
			return;
		}
		var s = a.getchecked();
		if(!s){
			js.msgerror('未选中复选框的任何记录');
			return;
		}
		this.querens(s);
		
	}
	c.querens = function(s,bo){
		if(!bo){
			var money = 0,da = a.getcheckdata(),i;
			for(i=0;i<da.length;i++){
				money += parseFloat(da[i].money);
			}
			js.confirm('确保已收到'+js.float(money)+'元了吗，是否点确认收款？', function(jg){
				if(jg=='yes')c.querens(s, true);
			});
			return;
		}
		js.msg('wait','处理中...');
		js.ajax(this.getacturl('orderqueren'), {ids:s}, function(){
			js.msg('success','处理成功');
			a.reload();
		},'post');
	}
	c.cogfin = function(o1){
		
		js.tanbody('finoptsotcks',o1.innerHTML,300,200,{
			html:'<div style="padding:10px">选择记账的财务帐号：<select id="optchangkude" class="form-control"><option value="">请选择...</option></select></div>',
			btn:[{text:'确定'}]
		});
		$('#finoptsotcks_btn0').click(function(){
			savecogfin();
		});
		
		if(typeof(zhangarrjodedata)=='object'){
			js.setselectdata(get('optchangkude'), zhangarrjodedata, 'value');
			if(zhangid)get('optchangkude').value = zhangid;
		}else{
			js.setmsg('加载中...','','msgview_finoptsotcks');
			$.ajax({
				url:js.getajaxurl('getzhang','mode_finfybx|input','flow'),
				dataType:'json',
				success:function(ret){
					js.setmsg('','','msgview_finoptsotcks');
					zhangarrjodedata = ret;
					js.setselectdata(get('optchangkude'), zhangarrjodedata, 'value');
					if(zhangid)get('optchangkude').value = zhangid;
				}
			});
		}
		
		function savecogfin(){
			var val = get('optchangkude').value;
			zhangid = val;
			$.get(c.getacturl('savezhangid')+'&zhangid='+val+'');
			js.tanclose('finoptsotcks');
		}
	}

	c.onloadbefore=function(d){
		zhangid = d.zhangid;
	}
	
	c.creagejizhang = function(){
		if(!zhangid){
			js.msgerror('没有设置默认收款的帐号');
			return;
		}
		if(atype!='alldjz'){
			js.msgerror('请切换到“待生成记账单”的列表下');
			return;
		}
		var s = a.getchecked();
		if(!s){
			js.msgerror('未选中复选框的任何记录');
			return;
		}
		var len = '生成'+(s.split(',').length)+'条记账单';
		js.confirm('确定要'+len+'吗？', function(jg){
			if(jg!='yes')return;
			js.loading(''+len+'...');
			js.ajax(c.getacturl('createjizhang'),{ids:s}, function(ret){
				if(ret.success){
					js.msgok(ret.data);
					a.reload();
				}else{
					js.msgerror(ret.msg);
				}
			}, 'get,json');
		});
	}
}

//[自定义区域end]
	c.initpagebefore();
	js.initbtn(c);
	var a = $('#view'+modenum+'_{rand}').bootstable(bootparams);
	c.init();
	
});
</script>
<!--SCRIPTend-->
<!--HTMLstart-->
<div>
	<table width="100%">
	<tr>
		<td style="padding-right:10px;" id="tdleft_{rand}" nowrap><button id="addbtn_{rand}" class="btn btn-primary" click="clickwin,0" disabled type="button"><i class="icon-plus"></i> <?=lang('新增')?></button></td>
		
		<td><select class="form-control" style="width:110px;border-top-right-radius:0;border-bottom-right-radius:0;padding:0 2px" id="fields_{rand}"></select></td>
		<td><select class="form-control" style="width:60px;border-radius:0px;border-left:0;padding:0 2px" id="like_{rand}"><option value="0"><?=lang('包含')?></option><option value="1"><?=lang('等于')?></option><option value="2"><?=lang('大于')?><?=lang('等于')?></option><option value="3"><?=lang('小于')?><?=lang('等于')?></option><option value="4"><?=lang('不包含')?></option></select></td>
		<td><select class="form-control" style="width:130px;border-radius:0;border-left:0;display:none;padding:0 5px" id="selkey_{rand}"><option value="">-<?=lang('请选择')?>-</option></select><input class="form-control" style="width:130px;border-radius:0;border-left:0;padding:0 5px" id="keygj_{rand}" placeholder="<?=lang('关键字')?>"><input class="form-control" style="width:130px;border-radius:0;border-left:0;padding:0 5px;display:none;" id="key_{rand}" placeholder="<?=lang('关键字')?>">
		</td>
		
		<td>
			<div style="white-space:nowrap">
			<button style="border-right:0;border-radius:0;border-left:0" class="btn btn-default" click="searchbtn" type="button"><?=lang('搜索')?></button><button class="btn btn-default" id="downbtn_{rand}" type="button" style="padding-left:8px;padding-right:8px;border-top-left-radius:0;border-bottom-left-radius:0"><i class="icon-angle-down"></i></button> 
			</div>
		</td>
		<td  width="90%" style="padding-left:10px"><div id="changatype{rand}" class="btn-group"></div></td>
	
		<td align="right" id="tdright_{rand}" nowrap>
			<span style="display:none" id="daoruspan_{rand}"><button class="btn btn-default" click="daoru,1" type="button"><?=lang('导入')?></button>&nbsp;&nbsp;&nbsp;</span><button class="btn btn-default" style="display:none" id="daobtn_{rand}" disabled click="daochu" type="button"><?=lang('导出')?> <i class="icon-angle-down"></i></button> 
		</td>
	</tr>
	</table>
</div>
<div class="blank10"></div>
<div id="viewfinorder_{rand}"></div>
<!--HTMLend-->