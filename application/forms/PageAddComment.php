<?php
class ShareIt_Form_PageAddComment extends Zend_Form {
	public function init()
	{
		$this->setMethod("post");
		
		$this->addElement("textarea", "content", array(
			'label' => "Add comment:",
			'required' => TRUE,
			'validators' => array(
				array('validator' => "StringLength", 'options' => array(5))
			)
		));
		
		$this->addElement("captcha", "captcha", array(
            'label'      => "Please enter the 5 letters displayed below:",
            'required'   => TRUE,
            'captcha'    => array(
                'captcha' => "Image",
                'wordLen' => 5,
                'timeout' => 300,
				'font' => APPLICATION_PATH . "/../library/fonts/verdana.ttf",
				'imgDir' => APPLICATION_PATH . "/../public/captcha/",
				'imgUrl' => "/captcha/"
            )
        ));
        
        $this->addElement("submit", "submitAddComment", array(
        	'label' => "Submit",
        	'ignore' => TRUE
        ));
	}
}