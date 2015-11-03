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
	  <h1>nijiGo|彩虹Go</h1>
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
	    <div id="breadcrumb"> <a href="#" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a> </div>
	  </div>
	  <div class="container-fluid">
		<div class="row-fluid">
		  <div class="span12">
	        <div class="widget-box">
	          <div class="widget-title"> <span class="icon"> <i class="icon-list"></i> </span>
	            <h5><?php echo L('TEXT_WELCOME_TITLE');?></h5>
	          </div>
	          <div class="widget-content"><?php echo L('TEXT_WELCOME_CONTENT');?></div>
	        </div>
	      </div>
	    </div>
	  </div>
	</div>
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