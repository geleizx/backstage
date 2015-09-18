<?php
class MessageboardModel extends RelationModel {
	
	protected $_link = array(
        'vistor' => array(
            'mapping_type'    =>HAS_ONE,
            'class_name'    => 'messageboard_vistor',
            'foreign_key' => 'mid',
            'mapping_name' => 'comment'
        ),
        'respond' => array(
            'mapping_type'    =>HAS_ONE,
            'class_name'    => 'messageboard_respond',
            'foreign_key' => 'mid',
            'mapping_name' => 'respond'
        )
	);
    
}
?>