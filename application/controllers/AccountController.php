<?php
class AccountController extends ShareIt_Controller_Front {

	public function signinAction()
	{
		$auth = $this->getAuthObject();
		if ($auth->hasIdentity())
			$this->_helper->redirector("index", "index");
		
		$form = new ShareIt_Form_Signin();
		
		$r = $this->getRequest();
		if ($r->isPost()) {
			$formData = $r->getPost();
			if ($form->isValid($formData)) {
				$adapter = ShareIt_Model_UsersMapper::getAuthAdapter($formData);
				$result = $auth->authenticate($adapter);
				if ($result->isValid()) {
					$storage = $auth->getStorage();
					$storage->write($adapter->getResultRowObject(NULL, "password"));
					$this->_helper->redirector("index", "index");
				}
			}
		}
		
		$this->view->form = $form;
	}
	
	public function signoutAction()
	{
		$auth = $this->getAuthObject();
		if ($auth->hasIdentity())
			$auth->clearIdentity();
			
		$this->_helper->redirector("index", "index");
	}
}
?>