<?php

class IntroductionAction extends AdminBaseAction {

    private $introductionIds ;
    private $id;

    public function __construct(){
        parent::__construct();
        $this->introductionIds = $this->_post('introductionIds');
        $this->id = $this->_get('id') | $this->_post('id');
    }

    /**
     *
     */
    public function index(){
        $introduction = M('Introduction');
        $result = $introduction->select();
        $this->assign('introductions',$result);
        $this->display("Tpl/Admin/default/introduction-managment.html");
    }

    /**
     *
     */
    public function create(){
        $this->display("Tpl/Admin/default/interduction-new.html");
    }


    /**
     *
     */
    public function edit(){
        $news = D('Introduction');
        $ret = $news->where(array("id" => $this->id))->find();
        $this->assign('introduction',$ret);
        $this->display("Tpl/Admin/default/interduction-new.html");
    }

    public function deleteIntroduction(){
        $news = D('Introduction');
        $ids = join($this->introductionIds,',');
        $news->delete($ids);
        if ($news->getError()){
            $this->error($news->getError());
        }
        $this->success('');
    }

    /**
     *
     */
    public function add(){
        $introduction = D('Introduction');

        $title = $this->_post('title');
        $content = $this->_post('editor');

        $description = $this->_post('description');
        $keywords = $this->_post('keywords');


        $date = date('Y-m-d h:i:s');

        $data = array('title' => $title , 'content' => $content , 'create_date' => $date ,
            'description' => $description , 'keywords' => $keywords ,'id' => $this->id);

        $ret = null;

        if(empty ($this->id)){
            $ret =  $introduction->add($data);
        }else{
            $ret = $introduction->create($data);
        }

        $introduction->save();


        if ($ret){
            $this->success($introduction->getDbError());
        }else{
            $this->error($introduction->getDbError());
        }

    }
}