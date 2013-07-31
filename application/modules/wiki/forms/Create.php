<?php

class Wiki_Form_Create extends Zend_Form
{

  public $ElementDecorators = array(
        'ViewHelper',
        array('Errors', array('class' => 'error')),
        array('Label', array('tag' => 'div','class'=>'fields-label')),
        array('HtmlTag', array('tag' => 'div', 'class' => 'el')),
        //array('HtmlTag', array('tag' => 'tr'))
    );
    public $EmptyDecorators = array(
        'ViewHelper',
    );

    public function init() {
        parent::init();
        $this->addPrefixPath('Mark_Form_Element_', 'Mark/Form/Element/', 'element'); //设置搜索Element的路径
        //$this->setConfig($config->form);
        $this->setDisableLoadDefaultDecorators(TRUE);
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));
        $this->addElement('text', 'title', array(
            'label' => 'Title:',
            'required' => TRUE,
            'decorators' => $this->ElementDecorators,
            'validators' => array(
                Custom_Tools_Validators::NotEmpty(),
                Custom_Tools_Validators::Db_NoRecordExists('wiki_topics', 'title')
            )
        ));

        $this->addElement('textarea', 'content', array(
            'label' => 'Content:',
            'required' => TRUE,
            'decorators' => $this->ElementDecorators,
            'validators' => array(
                Custom_Tools_Validators::NotEmpty()
            )
        ));
        $this->addElement('select', 'category', array(
            'label' => 'Category:',
            'required' => TRUE,
            'multiOptions'=>array('1'=>'type1', '2'=>'type2'),
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

