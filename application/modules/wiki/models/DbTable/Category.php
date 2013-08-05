<?php

class Wiki_Model_DbTable_Category extends Wiki_Model_DbTable_Abstract
{

    protected $_name = 'wiki_category';
    protected $_referenceMap = array(  
        'TopicsRef' => array(  
            'columns'       => 'id',  
            'refTableClass' => 'Wiki_Model_DbTable_Topics',  
            'refColumns'    => 'cid'  
        )  
    );
    
}

