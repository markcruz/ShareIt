<?php
class ShareIt_Form_PageDetail extends Zend_Form {
	public function init()
	{
		$this->setMethod("post");
		$this->setAttrib("enctype", "multipart/form-data");
		
		$this->addElement("file", "file", array(
			'label' => "File:",
			'required' => TRUE
		));
		
        $this->addElement("submit", "submitDetail", array(
        	'label' => "Submit",
        	'ignore' => TRUE
        ));
        
        $this->addElement("hash", "csrf", array(
        	'ignore' => TRUE
        ));
	}
}