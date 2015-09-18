<?php
class MenuModel extends RelationModel {

    protected $_link = array(
        'Childs'=>array(
            'mapping_type'    =>HAS_MANY,
            'class_name'    =>'Menu',
            'foreign_key' => 'id',
            'mapping_name' => 'childs',
            'parent_key' => 'parentid',
            'mapping_order' => 'listorder asc',
            'condition' => 'disabled != 1'
        ),
    );
	
	protected $fields = array(
			'id','module','name','uri','iscore','version','description','setting','listorder','disabled','installdate','updatedate', 'img' ,'parentid','isdropmenu', '_pk' => 'id', '_autoinc' => true
	);
}

?>