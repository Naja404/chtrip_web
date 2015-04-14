<?php
	return array(     
			'app_begin' => array('Behavior\CheckLangBehavior'),
			'app_init' =>  array('Behavior\UpgradeNoticeBehavior')
	);