<?php
class NewsModel extends RelationModel {
	
	protected $_link = array(
			'Category'=>array(
					'mapping_type'    =>BELONGS_TO,
					'class_name'    =>'Category',
					'foreign_key' => 'catid',
					'mapping_name' => 'category'
			),
        'NewsData' => array(
            'mapping_type'    =>HAS_ONE ,
            'class_name'    => 'NewsData',
            'foreign_key' => 'id',
            'mapping_name' => 'newsdata'
        )
	);
	
	
 	protected $fields = array(
 			'id',
 			'catid',
 			'typeid',
 			'title',
 			'style' ,
 			'thumb' ,
 			'keywords',
 			'description',
 			'posids' ,
 			'url' ,
 			'listorder',
 			'status' ,
 			'sysadd',
 			'islink' ,
 			'username',
 			'inputtime',
 			'updatetime',
 			'readtimes' ,
 			'ishot' ,
 			'counter' ,
 			'_pk' => 'id', '_autoinc' => true
 	);
}
