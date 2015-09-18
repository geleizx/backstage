<?php
class CategoryModel extends RelationModel{

    protected $_link = array(
        'Childs'=>array(
            'mapping_type'    =>HAS_MANY,
            'class_name'    =>'Category',
            'foreign_key' => 'catid',
            'mapping_name' => 'childs',
            'parent_key' => 'parentid',
            'mapping_order'=>'listorder desc',
        ),
        'Rss' => array(
            'mapping_type'    =>HAS_MANY ,
            'class_name'    => 'AdminRss',
            'foreign_key' => 'cat_id',
            'mapping_name' => 'rssdata'
        )
    );
}