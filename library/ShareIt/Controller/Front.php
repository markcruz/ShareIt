<?php
abstract class ShareIt_Controller_Front extends Zend_Controller_Action {
	/**
	 * Pages that offer authentication
	 * 
	 * @var array
	 */
	protected $authPages = array(
		'frontend' => array(
			'controller' => "account",
			'action' => "signin",
			'module' => "default"
		)
	);
	
	public function preDispatch()
	{
		parent::preDispatch();
		$auth = $this->getAuthObject();
		if ($auth->hasIdentity()) {
			$this->view->auth = $auth->getStorage()->read();
		} else
			$this->view->auth = NULL;
	}

	/**
	 * Returns the Zend_Auth instance,
	 * later possibly with a unique namespace for backend and frontend
	 * 
	 * @return Zend_Auth
	 */
	public function getAuthObject()
	{
		$type = "Frontend"; // this can be automatically determined by module name
		$auth = Zend_Auth::getInstance();
		$auth->setStorage(new Zend_Auth_Storage_Session("Zend_Auth_$type"));
		return $auth;
	}
}