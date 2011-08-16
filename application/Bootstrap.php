<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	public function run()
	{
		require APPLICATION_PATH . "/../library/NDebug/Debug.php";
		NDebug::enable(E_ALL ^ E_NOTICE);
		if ("production" == APPLICATION_ENV)
			NDebug::$email = "bugs@example.com";
		
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->setFetchMode(Zend_Db::FETCH_OBJ);
		
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace("ShareIt_"); 
		parent::run();
	}
}
