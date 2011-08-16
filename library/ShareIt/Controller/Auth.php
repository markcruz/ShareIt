<?php
abstract class ShareIt_Controller_Auth extends ShareIt_Controller_Front {
	public function preDispatch()
	{
		parent::preDispatch();
		$authPages = $this->authPages;
		$request = $this->getRequest();
		$module = $request->getModuleName();
		$redirectController = NULL;  
		$redirectAction = "index";
		switch ($module) {
			case $authPages['frontend']['module']:
				$redirectController = $authPages['frontend']['controller'];
				$redirectAction = $authPages['frontend']['action'];
			break;
			
			default:
				throw new UnexpectedValueException(
					sprintf("Module '%s' is not recognized by ShareIt_Auth.", $module)
				);
		}
		
		// Redirect unauthenticated users to login unless they are already there
		$auth = $this->getAuthObject();
		if (!$auth->hasIdentity()) {
			$grantAccessWithoutAuth = FALSE;
			$controller = $request->getControllerName();
			$action = $request->getActionName();
			
			// Exception to the rule - the page with login controller doesn't require any auth
			$authPage = $authPages['frontend'];
			$isMyAccount = $module == $authPage['module'] && $controller == $authPage['controller'] && $action == $authPage['action']; 
			if ($isMyAccount)
				$grantAccessWithoutAuth = TRUE;
			
			if (!$grantAccessWithoutAuth)
				$this->_helper->redirector->gotoSimpleAndExit($redirectAction, $redirectController, $module);
		}
	}
}
?>