<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 嘉懿
 * Date: 13-7-6
 * Time: 下午3:36
 * To change this template use File | Settings | File Templates.
 */

class PageModel extends RelationModel {
    protected $_link = array(
        'Category'=>array(
            'mapping_type'    =>BELONGS_TO,
            'class_name'    =>'Category',
            'foreign_key' => 'cid',
            'mapping_name' => 'category'
        ),
    );

}