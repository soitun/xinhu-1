<?php if(!defined('HOST'))die('not access');?>
<script >
$(document).ready(function(){
	var bools=false,mid=0;
	var a = $('#view_{rand}').bootstable({
		tablename:'flow_extent',celleditor:true,fanye:true,sort:'id',dir:'desc',
		url:publicstore('{mode}','{dir}'),storeafteraction:'afterstroesss',
		columns:[{
			text:'针对对象',dataIndex:'recename'
		},{
			text:'模块',dataIndex:'modename',sortable:true
		},{
			text:'类型',dataIndex:'type',sortable:true,renderer:function(oi){
				var as=['可查看','可添加','可编辑','可删除','可导入','可导出','禁看字段','流程监控','禁看处理记录','禁看查阅记录'];
				return ''+as[oi]+'';
			}
		},{
			text:'条件',dataIndex:'whereid'
		},{
			text:'并条件',dataIndex:'wherestr',align:'left',renderer:function(v){
				return jm.base64decode(v);
			}
		},{
			text:'说明',dataIndex:'explain',editor:true
		},{
			text:'状态',dataIndex:'status',type:'checkbox',editor:true,sortable:true
		},{
			text:'ID',dataIndex:'id'
		}],
		itemclick:function(d){
			mid=d.modeid;
			btn(false, d);
		},
		beforeload:function(){
			btn(true);
		}
	});
	function btn(bo, d){
		get('edit_{rand}').disabled = bo;
		get('del_{rand}').disabled = bo;
	}
	var c = {
		del:function(){
			a.del();
		},
		reload:function(){
			a.reload();
		},
		clickwin:function(o1,lx){
			if(mid==0){
				js.msg('msg','请先选择模块');
				return;
			}
			var icon='plus',name='新增流程模块权限',id=0;
			if(lx==1){
				id = a.changeid;
				icon='edit';
				name='编辑流程模块权限';
			};
			guanflowviewlist = a;
			addtabs({num:'flowview'+id+'',url:'main,view,edit,id='+id+',mid='+mid+'',icons:icon,name:name});
		},
		changemodes:function(v){
			mid=v;
			a.search('and modeid='+v+'');
		},
		xuanmode:function(o1){
			js.selectmode(o1, get('modes_{rand}'), function(sna,val,d){
				c.changemodes(val);
			});
		}
	};
	js.initbtn(c);
});
</script>

<div>
	<table width="100%">
	<tr>
	<td align="left">
		<button class="btn btn-warning" click="clickwin,0" type="button"><i class="icon-plus"></i> 新增</button>
	</td>
	<td style="padding-left:10px">
		<div class="btn-group"  style="width:260px;" click="xuanmode">
		<input class="input" placeholder="-选择模块-" style="flex:1" id="modes_{rand}" readonly>
		<button class="webbtn webbtn-default">v</button>
		</div>
	</td>
	<td width="90%">
		
	</td>
	<td nowrap>
		
		<button class="btn btn-info" id="edit_{rand}" click="clickwin,1" disabled type="button"><i class="icon-edit"></i> 编辑 </button>&nbsp; 
		<button class="btn btn-danger" click="del" disabled id="del_{rand}" type="button"><i class="icon-trash"></i> 删除</button>
	</td>
	</tr>
	</table>
	
</div>
<div class="blank10"></div>
<div id="view_{rand}"></div>
<div class="tishi">提示：多条将是或者的关系<div>
