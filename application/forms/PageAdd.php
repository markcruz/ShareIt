<?php
class ShareIt_Form_PageAdd extends Zend_Form {
	public function init()
	{
		$this->setMethod("post");
		$this->setAttrib("enctype", "multipart/form-data");
		
		$this->addElement("file", "file", array(
			'label' => "File:",
			'required' => TRUE
		));
		
		$this->addElement("textarea", "description", array(
			'label' => "Description:",
			'required' => TRUE,
			'validators' => array(
				array('validator' => "StringLength", 'options' => array(10))
			)
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