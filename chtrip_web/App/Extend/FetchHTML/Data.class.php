<?php
/**
* 数据处理
*/
require_once(dirname(__FILE__).'/../phpQuery.class.php');

class Data {
	
	public function __construct($filePath){
		phpQuery::newDocumentHTML($filePath); 
	}

}

?>
