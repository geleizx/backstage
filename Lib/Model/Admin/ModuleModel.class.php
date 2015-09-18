<?php
class ModuleModel extends Model {
	
	protected $fields = array(
			'id','module','name','uri','iscore','version','description','setting','listorder','disabled','installdate','updatedate', 'img' , '_pk' => 'id', '_autoinc' => true
	);
}

?>