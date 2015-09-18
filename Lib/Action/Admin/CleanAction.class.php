<?php

class CleanAction extends AdminBaseAction {
	public function index () {


		$DB_feedback_category = M('category');
		$categoryId = $this->_get("categoryid");
		$categoryList = $DB_feedback_category -> select();

        $this->assign("categoryList",$categoryList);  //输出分类

        $queryResult = $this->queryMessage($categoryId);

        $this->assign("page",$queryResult['pageShow']);// 分页显示输出
		$this->assign("feedbackList",$queryResult['messageList']); //输入留言列表
        $this->display();
	}


    private function queryMessage($catId="" ,$pre_page = "15"){

        $resultArr = array();

        $keyword = $this->_post('keyword');

        $where = array();

        if(!empty($keyword)){
            $where['title'] = array("LIKE","%$keyword%");
        }
        if(!empty($catId)){
            $where['categoryid'] = $catId;
        }

        $DB_clean = M('clean');
        $pre_page = $pre_page; //每一页显示多少条数据
        //ICE 分页
        import('ORG.Util.Page'); // 导入分页类

        $count = $DB_clean -> where($where) -> count();// 查询满足要求的总记录数
        $Page  = new Page($count,$pre_page);// 实例化分页类 传入总记录数和每页显示的记录数
        $messageList = $DB_clean  -> where($where) -> order("title desc") -> limit($Page->firstRow,$Page->listRows) -> select();


        $resultArr['messageList'] = $messageList;
        $resultArr['pageShow'] = $Page->show();

        return $resultArr;
    }

    public function ajaxSearch(){
        $keyword = "";


        $queryResult = $this->queryMessage();

        $this->assign("feedbackList",$queryResult['messageList']); //

        $tabelHtml   = $this->fetch("Tpl/Admin/default/Clean/clean-table.inc.html");

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
		$Id  = I('id');

		//获得删除ID
		$ids = I('ids');
		$DB_clean = M("clean");

		if($Id) {
			$res = $DB_clean -> where("`id` = $Id[0]") -> delete();
			if ($res) {
				$this->success('删除成功');
			}
			else {
				$this->error('删除失败');
			}
		}
		elseif ($ids) {
			$ids = implode($ids,',');
			$res = $DB_clean -> where("`id` IN ($ids)") -> delete();
			if ($res) {
				$this->success('删除成功');
			}
			else {
				$this->error('删除失败');
			}
		}
		else {
			$this->error('意外错误');
		}
		

	}




   //*********************修改**************************************/
    public function edit(){
        $id = I('id');
        $clean_model = M('clean');
        $clean = $clean_model->where(array( 'id' =>$id ) )->find();

        $this->assign('clean',$clean);
        $this->display();
    }
    //*********************添加**************************************/
    public function add(){
        $id = I('id');

        $data['title'] = I('title');
        $data['package'] = I('package');
        $data['folder'] = I('folder');
        $data['date'] =time();
        if($id){
            $res = M('clean')->where("id = $id")->save($data);
        }else{
            $res = M('clean')->add($data);
        }

        if ( ! $res){
            $this->error( M('clean')->getDbError());
        }else{
            $info =$id?'修改成功':'添加成功';
            $this->success($info,"__APP__/Admin/Clean/index");
        }

    }

}

?>