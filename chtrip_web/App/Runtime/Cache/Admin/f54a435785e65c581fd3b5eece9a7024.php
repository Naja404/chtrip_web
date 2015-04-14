<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo ($title); ?></title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/css/bootstrap.min.css" />
<link rel="stylesheet" href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/css/bootstrap-responsive.min.css" />
<link rel="stylesheet" href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/css/datepicker.css" />
<?php if(in_array((CONTROLLER_NAME), explode(',',"Login"))): ?><link rel="stylesheet" href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/css/matrix-login.css" /><?php endif; ?>
<?php if(in_array((CONTROLLER_NAME), is_array($header_list)?$header_list:explode(',',$header_list))): ?><link rel="stylesheet" href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/css/uniform.css" />
<link rel="stylesheet" href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/css/select2.css" />
<link rel="stylesheet" href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/css/matrix-media.css" />
<link rel="stylesheet" href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/css/matrix-style.css" /><?php endif; ?>
<link href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin//font-awesome/css/font-awesome.css" rel="stylesheet" />
<link href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin//font-awesome/font-family.css" rel="stylesheet" />
<?php if(in_array((CONTROLLER_NAME), explode(',',"Getui"))): ?><link rel="stylesheet" href="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/css/getui.css" /><?php endif; ?>
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/jquery.min.js"></script>

</head>
<body>

	<!--Header-part-->
	<div id="header">
	  <h1>withtrip|伴旅</h1>
	</div>
	<!--close-Header-part--> 

	<!--top-Header-menu-->
	<div id="user-nav" class="navbar navbar-inverse">
	  <ul class="nav">
	    <li  class="dropdown" id="profile-messages" ><a title="" href="#" data-toggle="dropdown" data-target="#profile-messages" class="dropdown-toggle"><i class="icon icon-user"></i>  <span class="text"><?php echo ($_adminUser); ?></span><!-- <b class="caret"></b> --></a>
<!-- 	      <ul class="dropdown-menu">
	        <li><a href="#"><i class="icon-user"></i> My Profile</a></li>
	        <li class="divider"></li>
	        <li><a href="#"><i class="icon-check"></i> My Tasks</a></li>
	        <li class="divider"></li>
	        <li><a href="<?php echo U('Login/logout');?>"><i class="icon-key"></i> Log Out</a></li>
	      </ul> -->
	    </li>
<!-- 	    <li class="dropdown" id="menu-messages"><a href="#" data-toggle="dropdown" data-target="#menu-messages" class="dropdown-toggle"><i class="icon icon-envelope"></i> <span class="text"><?php echo L('text_message');?></span> <span class="label label-important">5</span> <b class="caret"></b></a>
	      <ul class="dropdown-menu">
	        <li><a class="sAdd" title="" href="#"><i class="icon-plus"></i> new message</a></li>
	        <li class="divider"></li>
	        <li><a class="sInbox" title="" href="#"><i class="icon-envelope"></i> inbox</a></li>
	        <li class="divider"></li>
	        <li><a class="sOutbox" title="" href="#"><i class="icon-arrow-up"></i> outbox</a></li>
	        <li class="divider"></li>
	        <li><a class="sTrash" title="" href="#"><i class="icon-trash"></i> trash</a></li>
	      </ul>
	    </li> 
	    <li class=""><a title="" href="#"><i class="icon icon-cog"></i> <span class="text"><?php echo L('text_setting');?></span></a></li> -->
	    <li class=""><a title="" href="<?php echo U('Login/logout');?>"><i class="icon icon-share-alt"></i> <span class="text"><?php echo L('text_logout');?></span></a></li>
	  </ul>
	</div>

	<!--start-top-serch-->
	<div id="search">
	  <input type="text" placeholder="Search here..."/>
	  <button type="submit" class="tip-bottom" title="Search"><i class="icon-search icon-white"></i></button>
	</div>
	<!--close-top-serch--> 
  <div id="sidebar"> <a href="#" class="visible-phone"><i class="icon icon-th"></i>Tables</a>
    <ul>
      <?php if(is_array($_adminLeftMenu)): $i = 0; $__LIST__ = $_adminLeftMenu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li class="submenu <?php if((CONTROLLER_NAME) == $item['module']): ?>active open<?php endif; ?>">
        <a href="#"><i class="icon icon-th"></i> <span><?php echo ($item["title"]); ?></span></a>
        <ul>
          <?php if(is_array($item["list"])): $i = 0; $__LIST__ = $item["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><li><a href="<?php echo U($v['url']);?>"><?php echo ($v["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
      </li><?php endforeach; endif; else: echo "" ;endif; ?>
    </ul>
  </div>
	<div id="content">
	  <div id="content-header">
	    <div id="breadcrumb"> <a href="<?php echo U('Index/index');?>" title="<?php echo L('text_goto_home');?>" class="tip-bottom"><i class="icon-home"></i> <?php echo L('text_home');?></a> <a><?php echo L('text_setting');?></a> <a href="<?php echo U('Setting/menuList');?>" class="current"><?php echo L('text_title_user_list');?></a> </div>
	  </div>
	  <div class="container-fluid">
	    <div class="row-fluid">
	      <div class="span12">
	        <div class="widget-box">
	          <div class="widget-title"> <span class="icon"> <i class="icon-th"></i> </span>
	            <h5><?php echo L('text_user_list');?></h5>
	            <span class="label label-info">
	            	<a data-toggle="modal" href="#modal-add-user" id="modal_show"><i class="icon-plus icon-white"></i><?php echo L('btn_add_user');?></a>
	            </span>
	          </div>
	          <div class="widget-content nopadding">
	            <table class="table table-bordered table-striped">
	              <thead>
	                <tr>
	                  <th><?php echo L('text_user_name_email');?></th>
	                  <th><?php echo L('text_user_group');?></th>
	                  <th><?php echo L('text_user_create');?></th>
	                  <th><?php echo L('text_setting_action');?></th>
	                </tr>
	              </thead>
	              <tbody>
	              	<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><tr class="odd gradeX">
	                  <td><span style=""><?php echo ($item["name"]); ?></span> / <?php echo ($item["email"]); ?></td>
	                  <td><?php echo ($item["title"]); ?></td>
	                  <td><?php echo (date("Y-m-d H:i:s",$item["created"])); ?></td>
	                  <td id="status_<?php echo ($item["id"]); ?>">
	                  	<input type="button" class="btn btn-danger" name="" value="<?php echo L('btn_suemail_del');?>" onclick="delUser('<?php echo ($item["uid"]); ?>');return false;"/>
	                  	<input type="button" class="btn btn-info" name="" value="<?php echo L('btn_menu_edit');?>" onclick="getUserDetail('<?php echo ($item["uid"]); ?>');return false;"/>
	                  </td>
	                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
	              </tbody>
	            </table>
	          </div>
	        </div>
	      </div>
			<div class="pagination alternate" style="float:right;">
				<?php echo ($page_show); ?>
			</div>
	    </div>
	  </div>
	</div>

	<div class="modal hide" id="modal-add-user">
	<form action="" method="post" id="addUserForm">
	<div class="modal-header">
	  <button type="button" class="close" data-dismiss="modal" id="close_modal_btn">×</button>
	  <h3><?php echo L('btn_add_user');?></h3>
	</div>
	<div class="modal-body">

		<p><?php echo L('text_user_name');?></p>
		<p>
			<input type="text" name="name" value="" placeholder="<?php echo L('input_user_name');?>" />
		</p> 

		<p><?php echo L('text_user_password');?></p>
		<p>
			<input type="password" name="password" value="" placeholder="<?php echo L('input_user_password');?>" />
		</p>

		<p><?php echo L('text_user_email');?></p>
		<p>
			<input type="text" name="email" value="" placeholder="<?php echo L('input_user_email');?>" />
		</p> 

		<p><?php echo L('text_user_type');?></p>
		<p>
			<select name="group_id" style="width:100px;">
				<?php if(is_array($group_list)): $i = 0; $__LIST__ = $group_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><option value="<?php echo ($item["id"]); ?>"><?php echo ($item["title"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
			</select>
		</p> 

		<input type="hidden" name="uid" value="" />
	</div>
	<div class="modal-footer"> 
		<a href="#" class="btn" data-dismiss="modal" id="dismiss_modal"><?php echo L('btn_cancel');?></a> <a href="#" id="add-user-submit" class="btn btn-primary"><?php echo L('btn_submit');?></a>
	</div>
	</form>
	</div>
<script type="text/javascript">
$(document).ready(function(){

	$("#add-user-submit").click(function(){
		subForm();
	});

	$("#close_modal_btn").click(function(){
		$("input[name='uid']").val("");
		$('#addUserForm')[0].reset();
	});

	$("#modal_show").click(function(event, isNew){
		if (!isNew) {
			$("input[name='id']").val("");
			$('#addMenuForm')[0].reset();
		}
	});

});

function getUserDetail(uid){
	$.ajax({
		type:"POST",
		url:"<?php echo U('Setting/getUserDetail');?>",
		data:{uid:uid},
		success:function(data){
			if (data.status) {
				$('#addUserForm')[0].reset();
				$('input[name=name]').val(data.info['name']);
				$('input[name=email]').val(data.info['email']);
				$('input[name=group_id]').val(data.info['is_display']);
				$('input[name=uid]').val(data.info['uid']);
				$("#modal_show").trigger("click", [1]);
			}else{
				alert(data.msg);
			}
		}	
	});
}

function subForm(){
	$.ajax({
		type:"POST",
		url:"<?php echo U('Setting/setUserForm');?>",
		data:$('#addUserForm').serialize(),
		success:function(data){
			show_error(data.msg, data.status);
			if (data.status) {
				setTimeout('$("#dismiss_modal").trigger("click");', 2000);
				location.reload();
				// $('#addUserForm')[0].reset();
			}
		}	
	});
}

function delUser(uid){
	if (confirm("<?php echo L('text_user_del_confirm');?>")) {
		$.ajax({
			type:"POST",
			url:"<?php echo U('Setting/delUser');?>",
			data:{uid:uid},
			success:function(data){
				location.reload();
			}	
		});
	}
}

function show_error(info, type){

	var color = type == 1 ? '#51a351' : '#f00';

	$('#modal-error').remove();
	$('<div style="border-radius: 5px; top: 70px; font-size:14px; left: 50%; margin-left: -70px; position: absolute;width: 150px; background-color: '+ color +'; text-align: center; padding: 5px; color: #ffffff;" id="modal-error">'+ info +'</div>').appendTo('#modal-add-user .modal-body');
	$('#modal-error').delay('1500').fadeOut(700,function() {
		$(this).remove();
	});
}
</script>
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/jquery.ui.custom.js"></script> 
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/bootstrap.min.js"></script> 
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/bootstrap-colorpicker.js"></script> 
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/bootstrap-datepicker.js"></script> 
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/jquery.uniform.js"></script>
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/masked.js"></script>  
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/select2.min.js"></script> 
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/jquery.dataTables.min.js"></script> 
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/jquery.validate.js"></script> 
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/matrix.js"></script> 
<!-- <script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/matrix.form_common.js"></script>  -->
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/matrix.tables.js"></script>
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/matrix.login.js"></script> 
<script src="<?php echo C('ADMIN_WEBSITE');?>/Public/admin/js/matrix.form_validation.js"></script>
</body>
</html>