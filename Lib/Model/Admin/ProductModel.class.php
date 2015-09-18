<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Gavin
 * Date: 13-4-16
 * Time: 下午2:26
 *
 */

class ProductModel extends RelationModel {

    protected $_link = array(
        'Category'=>array(
            'mapping_type'    =>BELONGS_TO,
            'class_name'    =>'Category',
            'foreign_key' => 'category_id',
            'mapping_name' => 'category'
        ),
    );
}