<?php
/**
 * Created by PhpStorm.
 * User: mackcyl
 * Date: 14-3-31
 * Time: 下午2:52
 */

//namespace Admin\Model;

class MemberModel extends RelationModel {

    protected $tableName = 'member';

    /**
     * 设置会员与会员组关联关系模型
     * @var array
     */
    protected $_link = array(
        'Group'=>array(
            'mapping_type'    =>BELONGS_TO,
            'class_name'    =>'member_group',
            'foreign_key' => 'groupid',
            'mapping_name' => 'group'
        )
    );


    /**
     * 取会员列表
     * @param int $groupId
     * @param string $whereParam
     * @param string $order
     * @return mixed
     */
    public function getMemberList($groupId=0,$whereParam='',$order=' regdate desc '){

        $where = $whereParam;

        $where['vip'] = array('gt',0);
        if($groupId > 0){
            $where['groupid'] = $groupId;
        }
       $result =  $this->relation(true)->where($where)->order($order)->select();
        foreach($result as $key=>$value){
            $memberId = $result[$key]['userid'];
            $memberDetail = M('member_detail')->where("userid = ".$memberId)->find();
            $result[$key]['memberDetail'] = $memberDetail;
        }
        return $result;

    }

    public function update($data){
        $data = $this->create($data);
        /* 获取数据对象 */
        if(empty($data)){
            return false;
        }
        /* 添加或新增基础内容 */
        if(empty($data['userid'])){ //新增数据
            $id = $this->add(); //添加基础内容
            if(!$id){
                $this->error = '新增出错！';
                return false;
            }
        } else { //更新数据
            $status = $this->save(); //更新基础内容
            if(false === $status){
                $this->error = '更新出错！';
                return false;
            }
        }
        // 清除模型缓存数据

        //内容添加或更新完成
        return $data;
    }
} 