<?php
/*
 * ICE 2013-06-27
 * 留言管理部分操作 @显示管理列表 @回复功能 @删除留言 @删除回复
 * 
 */
class MessageAction extends AdminBaseAction {
	public function index () {
		//显示留言管理列表

		$DB_feedback_category = M('category');
		$categoryId = $this->_get("categoryid");
		$categoryList = $DB_feedback_category -> select();
	
		/*
		$DB_messageboard = D('Messageboard');

		$pre_page = 10;//每一页显示多少条数据
		//ICE 分页
		import('ORG.Util.Page');// 导入分页类
		
		if ($categoryId) {
			//查询某类下留言
			$count = $DB_messageboard -> where("`categoryid` = $categoryId") -> count();// 查询满足要求的总记录数
			$Page  = new Page($count,$pre_page);// 实例化分页类 传入总记录数和每页显示的记录数
			$messageList = $DB_messageboard -> relation(true) -> where("`categoryid` = $categoryId") -> order("date desc") -> limit($Page->firstRow,$Page->listRows) -> select();
		}
		else {
			//查询所有留言
			$count = $DB_messageboard -> where("`type` = 0") -> count();// 查询满足要求的总记录数
			$Page  = new Page($count,$pre_page);// 实例化分页类 传入总记录数和每页显示的记录数
			$messageList = $DB_messageboard -> relation(true) -> where("`type` = 0") -> order("date desc") -> limit($Page->firstRow,$Page->listRows) -> select();
		}


		//dump ($messageList);
		$show = $Page->show();// 分页显示输出
	//	dump ($show);

*/
        $this->assign("categoryList",$categoryList);  //输出分类

        $queryResult = $this->queryMessage($categoryId);

        $this->assign("page",$queryResult['pageShow']);// 分页显示输出
		$this->assign("feedbackList",$queryResult['messageList']); //输入留言列表
        $this->display();
	}


    private function queryMessage($catId="" ,$pre_page = "10"){

        $resultArr = array();

        $keyword = $this->_post('keyword');

        $where = array();

        if(!empty($keyword)){
            $where['title'] = array("LIKE","%$keyword%");
        }
        if(!empty($catId)){
            $where['categoryid'] = $catId;
        }

        $DB_messageboard = D('Messageboard');
        $pre_page = $pre_page; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类

        $count = $DB_messageboard -> where($where) -> count();// 查询满足要求的总记录数
        $Page  = new Page($count,$pre_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $messageList = $DB_messageboard -> relation(true) -> where($where) -> order("date desc") -> limit($Page->firstRow,$Page->listRows) -> select();


        $resultArr['messageList'] = $messageList;
        $resultArr['pageShow'] = $Page->show();

        return $resultArr;
    }

    public function ajaxSearch(){
        $keyword = "";


        $queryResult = $this->queryMessage();


        $this->assign("feedbackList",$queryResult['messageList']); //输入留言列表

        $tabelHtml   = $this->fetch("Tpl/Admin/default/Message/message-table.inc.html");

        $ajaxResult = array("tabelHtml"=>$tabelHtml ,'pagination'=> $queryResult['pageShow']);

        $this->success($ajaxResult);
    }

	public function addRespondAct () {
		//在某条留言下追加一条回复
		$id = $this->_post('id');
		//获得操作id
		$content = $this->_post('content');
		//获得回复内容

		$DB_messageboard_respond = M('messageboard_respond');

		$data['content'] = $content;
		$data['mid'] = $id;
		$data['date'] = strtotime(date('Y-m-d'));

		$res = $DB_messageboard_respond -> add($data);
		if ($res) {
			$this->success('回复成功');
		}
		else {
			$this->error('回复失败');
		}
	}

	public function dropMessageAct () {
		//单条删除留言操作
		$productIds = $this->_post('productIds');
		//获得删除ID
		$ids = $this->_post('ids');
		$DB_messageboard = M("messageboard");
		$DB_messageboard_vistor = M("messageboard_vistor");
		if($productIds) {
			$res = $DB_messageboard -> where("`id` = $productIds[0]") -> delete();
			$resVistor = $DB_messageboard_vistor -> where("`mid` = $productIds[0]") -> delete();
			if ($res && $resVistor) {
				$this->success('删除成功');
			}
			else {
				$this->error('删除失败');
			}
		}
		else if ($ids) {
			$ids = implode($ids,',');
			$res = $DB_messageboard -> where("`id` IN ($ids)") -> delete();
			$resVistor = $DB_messageboard_vistor -> where("`mid` IN ($ids)") -> delete();
			if ($res && $resVistor) {
				$this->success('删除成功');
			}
			else {
				$this->error('删除失败');
			}
		}
		else {
			$this->error('意外错误');
		}
		
		
		/*$res = $DB_messageboard -> where("`mainID` = $id") -> delete();
		if ($res) {
			$this->success('删除成功');
		}
		else {
			$this->error('删除失败');
		}*/
	}
	public function verifyMessageAct () {
		//审核消息
		$id = $this->_post('id');
		//获得传入ID

		$DB_messageboard = M('messageboard');
		$verify = $DB_messageboard -> where("`id` = $id") -> field('verify') -> find();
		//echo $DB_messageboard -> getLastSql();
		//dump ($verify);
		if ($verify['verify'] == 1) {
			$data['verify'] = 0 ;
		}
		else {
			$data['verify'] = 1 ;
		}	
		$res = $DB_messageboard -> where("`id` = $id") -> save($data);
		// echo $DB_messageboard -> getLastSql();

        $status = $data['verify'];
		
		if ($res) {
			$this->success($status);
		}
		else {
			$this->error('操作失败');
		}
		
	}


    public function getRespond () {
        //在某条留言下追加一条回复
        $id = $this->_get('id');
        $DB_messageboard_respond = M('messageboard_respond');

        $res = $DB_messageboard_respond -> where( array('mid' => $id)) ->find();

        $this->success($res);
    }



	public function delRespondAct () {
		//删除回复页面
	}

	public function getNotRespondNum () {
	/*
	 * ICE 2013-07-06 获得未回复的留言数量
	 * @param 
	 */
		// $DB_messageboard = new Model(); //ICE 建立新模型
		// $dbPre =  C('DB_PREFIX'); //读取数据库前缀
		// $sql = "select count(content) as respondedNum from {$dbPre}messageboard LEFT JOIN {$dbPre}messageboard_respond ON {$dbPre}messageboard_respond.MID = {$dbPre}messageboard.mainID";
		// $res = $DB_messageboard -> query($sql);
		// $sql = $DB_messageboard -> getLastSql();

		$DB_messageboard = D('Messageboard');
		$messageList = $DB_messageboard -> relation(true) -> where("`type` = 0") -> select();

		//查询获得回复的条数
		foreach ($messageList as $value) {
			if ($value['respond']) {
		 	$respond[] = $value['respond'];}
		}
		$notRespondNum = count($messageList) - count($respond);
		
		return $notRespondNum;
	}


}

?>