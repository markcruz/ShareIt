<?php
class ShareIt_Form_Signin extends Zend_Form
{
    public function init()
    {
        $this->setMethod("post");
        $this->addElement("text", "email", array(
        	'label' => "Email:",
        	'required' => TRUE,
        	'filters' => array('StringTrim'),
        	'validators' => array(
        		'EmailAddress'
        	)
        ));
        
        $this->addElement("password", "password", array(
        	'label' => "Password:",
        	'required' => TRUE,
        	'filters' => array('StringTrim'),
        	'validators' => array(
        		array('validator' => "NotEmpty")
        	)
        ));
        
        $this->addElement("submit", "submit", array(
        	'label' => "Sign in",
        	'ignore' => TRUE
        ));
        
        $this->addElement("hash", "csrf", array(
        	'ignore' => TRUE
        ));
    }
}
