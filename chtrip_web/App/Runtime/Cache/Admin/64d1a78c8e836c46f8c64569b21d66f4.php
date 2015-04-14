<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
<title><?php echo ($title); ?></title><meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="/Public/admin/css/bootstrap.min.css" />
<link rel="stylesheet" href="/Public/admin/css/bootstrap-responsive.min.css" />
<link rel="stylesheet" href="/Public/admin/css/matrix-login.css" />
<link href="/Public/admin//font-awesome/css/font-awesome.css" rel="stylesheet" />
<link href='/Public/admin//font-awesome/font-family.css' rel='stylesheet' type='text/css'>
<script src="/Public/admin/js/jquery.min.js"></script>
</head>
    <body>
        <div id="loginbox">            
            <form id="loginform" class="form-vertical" action="<?php echo U('Login/login');?>" method="post">
				 <div class="control-group normal_text"> <h3><img src="/Public/admin/images/logo2.png" alt="Logo" /></h3></div>
                <div class="control-group">
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_lg"><i class="icon-user"></i></span><input type="text" placeholder="<?php echo L('login_username_input');?>" name="username" />
                        </div>
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_ly"><i class="icon-lock"></i></span><input type="password" placeholder="<?php echo L('login_password_input');?>" name="password" />
                        </div>
                    </div>
                </div>

                <div style="text-align:center;font-weight:bold;color:#963E3E;" >
                    <span id="error_text"><?php echo ($login_error); ?></span>
                </div>
                
                <div class="form-actions">
                    <span class="pull-left"><a href="#" class="flip-link btn btn-info" id="to-recover"><?php echo L('login_lost_password_btn');?></a></span>
                    <span class="pull-right"><a type="submit" onclick="submitLogin();return false;" class="btn btn-success" /> <?php echo L('login_login_btn');?></a></span>
                </div>
            </form>
            <form id="recoverform" action="#" class="form-vertical">
				<p class="normal_text"><?php echo L('login_lost_password_text');?></p>
				
                    <div class="controls">
                        <div class="main_input_box">
                            <span class="add-on bg_lo"><i class="icon-envelope"></i></span><input type="text" placeholder="<?php echo L('login_email_address_input');?>" />
                        </div>
                    </div>
               
                <div class="form-actions">
                    <span class="pull-left"><a href="#" class="flip-link btn btn-success" id="to-login"><?php echo L('login_back_login_btn');?></a></span>
                    <span class="pull-right"><a class="btn btn-info"/><?php echo L('login_password_recover_btn');?></a></span>
                </div>
            </form>
        </div>
<script type="text/javascript">
    var error_text = $('#error_text');

    $(document).ready(function(){
        if (error_text.text()) {
            error_text.fadeOut(4000);
        }
    });

    $(function(){
        $(document).keydown(function(event){
            if (event.keyCode == 13) {
                submitLogin();
            }
        });
    });

    function submitLogin(){
        var name = $('input[name=username]').val();
        var pwd = $('input[name=password]').val();

        if (!name) {
            alert('用户名不能为空');
            return false;
        }

        if (!pwd) {
            alert('密码不能为空');
            return false;
        }

        $('#loginform').submit();
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