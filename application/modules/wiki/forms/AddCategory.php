<?php 

class Wiki_Form_AddCategory extends Zend_Form {

	public $ElementDecorators = array(
        'ViewHelper',
        array('Errors', array('class' => 'error')),
        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
	    array('Label', array('tag' => 'td')),
	    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'zend_row'))
    );
    public $EmptyDecorators = array(
        'ViewHelper',
    );

	public function init(){
		parent::init();
		$this->setDisableLoadDefaultDecorators(TRUE);
		$this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'table')),
            'Form',
        ));
		$this->addElement('text', 'cname', array(
            'label' => 'Category Name:',
            'value' =>'Please Enter Category Name',
            'required' => TRUE,
            'decorators' => $this->ElementDecorators,
            'attribs' => array ('style' => 'width: 100%; position: relative'),
            'validators' => array(
                Custom_Tools_Validators::NotEmpty()
            )
        ));
		$this->addElement('text', 'status', array(
            'label' => 'Status:',
            'value' =>'Please Enter Category Status',
            'required' => TRUE,
            'decorators' => $this->ElementDecorators,
            'attribs' => array ('style' => 'width: 100%; position: relative'),
            'validators' => array(
                Custom_Tools_Validators::NotEmpty()
            )
        ));
		$this->addElement('text', 'parent_id', array(
            'label' => 'Parent ID:',
            'value' =>'Please Enter Parent ID',
            'required' => TRUE,
            'decorators' => $this->ElementDecorators,
            'attribs' => array ('style' => 'width: 100%; position: relative'),
            'validators' => array(
                Custom_Tools_Validators::NotEmpty()
            )
        ));
		
		$this->addElement('hidden', 'category_id', array(
				'value' =>'Please Enter Parent ID',
				'decorators' => $this->ElementDecorators,
				 'validators' => array(
                Custom_Tools_Validators::NotEmpty()
          	)
		));
		
		$this->addElement('submit', 'submit', array(
			'label' => 'Submit',
            'decorators' => $this->EmptyDecorators
        ));

	}

}

?>