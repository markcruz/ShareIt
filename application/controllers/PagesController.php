<?php
class PagesController extends ShareIt_Controller_Auth {
	public function detailAction()
	{
		$r = $this->getRequest();
		$pageId = $r->getParam("id");
		if ($pageId) {
			$this->view->heading = "Page Detail";
			$form = new ShareIt_Form_PageDetail();
		} else
			$form = new ShareIt_Form_PageAdd();
		
		// process file uploads
		if ($r->isPost() && $r->getParam("submitDetail")) {
			$formData = $r->getPost();
			if ($form->isValid($formData)) {
				$pageFileId = NULL;
				if ($pageId) {
					$mapper = new ShareIt_Model_PageFilesMapper();
					$detail = $mapper->getDetail($pageId);
					$pageFileId = $detail->id;
				}
				
                $mapper = new ShareIt_Model_PagesMapper();
                $mapper->save($formData, $form, $pageFileId);
                if (!$pageId)
                	$this->_helper->redirector("listing", "pages", "default", array('id' => $pageId));
			}
		}
		
		$this->view->form = $form;
		// process comments
		if ($pageId) {
			$this->view->pageId = $pageId;
			$form = new ShareIt_Form_PageAddComment();
			if ($r->isPost() && $r->getParam("submitAddComment")) {
				$postData = $r->getPost();
				if ($form->isValid($postData)) {
					$mapper = new ShareIt_Model_PageCommentsMapper();
					$mapper->save($pageId, $postData);
				}	
			}
			
			$mapper = new ShareIt_Model_PagesMapper();
			$this->view->pageDetail = $mapper->getDetail($pageId);
			
			$mapper = new ShareIt_Model_PageCommentsMapper();
			$this->view->comments = $mapper->getListing($pageId);
			
			$mapper = new ShareIt_Model_PageFilesMapper();
			$detail = $mapper->getDetail($pageId);
			
			$mapper = new ShareIt_Model_FileRevisionsMapper();
			$this->view->revisions = $mapper->getListing($detail->id);
			
			$this->view->formAddComment = $form;
		}
	}
	
	public function listingAction()
	{
		$mapper = new ShareIt_Model_PagesMapper();
    	$entries = $mapper->getListing();
    	$this->view->entries = $entries;
	}
	
	public function deleteAction()
	{
		$r = $this->getRequest();
		$pageId = $r->getParam("id");
		if ($pageId) {
			$mapper = new ShareIt_Model_PagesMapper();
			$mapper->delete($pageId);
		}
		
		$this->_helper->redirector("listing");
	}
	
	public function downloadAction()
	{
		$r = $this->getRequest();
		$id = $r->getParam("id");
		
		if ($id) {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(TRUE);
			$mapper = new ShareIt_Model_FileRevisionsMapper();
			$entry = $mapper->getDetail($id);
			
			header("Content-length: " . $entry->size);
			header("Content-type: " . $entry->mimetype);
			header("Content-disposition: attachment; filename=" . $entry->filename);
			echo $entry->content;
		}
	}
	
	public function notificationsAction()
	{
		$this->view->heading = "User Notifications";
	}
}