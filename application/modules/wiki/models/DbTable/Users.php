<?php

class Wiki_Model_DbTable_Users extends Wiki_Model_DbTable_Abstract
{

    protected $_name = 'users';
    protected $_referenceMap = array(  
        'TopicsRef' => array(  
            'columns'       => 'id',  
            'refTableClass' => 'Wiki_Model_DbTable_Topics',  
            'refColumns'    => 'uid'  
        ),
        'ContentRef' => array(  
            'columns'       => 'id',  
            'refTableClass' => 'Wiki_Model_DbTable_Contents',  
            'refColumns'    => 'uid'  
        )
    );
}

