<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mackcyl
 * Date: 13-10-24
 * Time: 下午5:58
 * To change this template use File | Settings | File Templates.
 */

class GroupModel extends RelationModel {

    protected $_link = array(
        'group_role'=>array(
            'mapping_type'    =>HAS_MANY,
            'class_name'    =>'group_role',
            'foreign_key' => 'group_id',
            'mapping_name' => 'roles'
        ),
    );

}