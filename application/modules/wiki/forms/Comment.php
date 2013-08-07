<?php

class Wiki_Form_Comment extends Zend_Form
{

  public $ElementDecorators = array(
        'ViewHelper',
        array('Errors', array('class' => 'error')),
        //array('HtmlTag', array('tag' => 'tr'))
    );
    public $EmptyDecorators = array(
        'ViewHelper',
    );

    public function init() {
        parent::init();
        //$this->addPrefixPath('Mark_Form_Element_', 'Mark/Form/Element/', 'element'); //设置搜索Element的路径
        //$this->setConfig($config->form);
        $this->setDisableLoadDefaultDecorators(TRUE);
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));
        $this->addElement('textarea', 'content', array(
            //'label' => 'Content:',
            'required' => TRUE,
            'decorators' => $this->ElementDecorators,
            'validators' => array(
                Custom_Tools_Validators::NotEmpty()
            )
        ));
         $this->addElement('submit', 'submit', array(
            'decorators' => $this->EmptyDecorators
        ));
    }


}

