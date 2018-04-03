<?php
namespace app\admin\controller;


class News extends Base
{

    public function __construct() {

    	parent::__construct();
    	$this->cate = D("Cate");
    	$this->news = D("News");
    }
    /**
     * 首页渲染
     */
	public function index() {

		$where['a.c_isdel'] = 0;
		$count = $this->news->alias('a')->where($where)->count();
        #实例化think分页类
        $Page = new \Think\Page($count,10);
        $show       = $Page->show();// 分页显示输出
        $list = $this->news->field('a.*,b.c_title as c_parent_title')
                ->alias('a')->join('LEFT JOIN __CATE__ b ON a.c_cateid=b.c_id')
                ->where($where)
                ->order('c_id asc')
                ->limit($Page->firstRow,$Page->listRows)->select();
		$this->assign([
			'title' => '资讯列表',
			'list' => $list,
			'page' => $show
			]);
		$this->display();
	}
    /**
     * 渲染添加页面
     */
	public function add() {
        
        $cate_list = $this->cate->field('c_id,c_title,c_parent_id')->catetree();

		$this->assign([
			'title' => '添加资讯',
			'cate_list' => $cate_list
			]);
		$this->display();
	}

	/**
	 * 执行添加
	 */
	public function doAdd() {
        if (IS_POST) {
        	$arr = $_POST;
            //接收数据
            $data = array(
                'c_title' => I('post.c_title'),
                'c_introduction' => I('post.c_introduction'),
                'c_detail' => $arr['c_detail'],
                'c_image' => I('c_image'),
                'c_cateid' => I('c_cateid'),
                //'c_pc_move' => I('c_pc_move'),
                'c_move_banner' => I('c_move_banner'),
                'c_recommend' => I('c_recommend'),
                //'c_wonderful' => I('c_wonderful'),
                'c_type' => I('c_type'),
                'c_video' => I('c_video'),
                'c_add_time' => time()
                );
            if ($this->news->create($data)) {
            	if ($this->news->add()) {
	                $this->ajaxReturn([
	                    'msg' => '添加成功', 
	                    'status' => 0
	                    ]);
	            }else {
	                $this->ajaxReturn([
	                    'msg' => '添加失败', 
	                    'status' => 1
	                    ]);
	            }
            }
            $this->ajaxReturn([
                'msg' => $this->news->getError(), 
                'status' => 1
                ]);
        }
	}

	/**
	 * 修改页面渲染
	 */
	public function edit($id) {

		$list = $this->news->where(['c_id' => $id])->find();
		$cate_list = $this->cate->field('c_id,c_title,c_parent_id')->where(['c_status' => 0])->catetree();

		$this->assign(array(
			'title' => '修改资讯',
			'list' => $list,
			'cate_list' => $cate_list
			));
		$this->display();
	}

	/**
	 * 执行修改
	 */
	public function doEdit() {
        if (IS_POST) {
        	$arr = $_POST;
            //接收数据
            $data = array(
            	'c_id' => I('post.c_id'),
                'c_title' => I('post.c_title'),
                'c_introduction' => I('post.c_introduction'),
                'c_detail' => $arr['c_detail'],
                'c_image' => I('c_image'),
                'c_cateid' => I('c_cateid'),
                //'c_pc_move' => I('c_pc_move'),
                'c_move_banner' => I('c_move_banner'),
                'c_recommend' => I('c_recommend'),
                //'c_wonderful' => I('c_wonderful'),
                'c_type' => I('c_type'),
                'c_video' => I('c_video'),
                );
            if ($this->news->create($data)) {
            	if ($this->news->save()) {
	                $this->ajaxReturn([
	                    'msg' => '修改成功', 
	                    'status' => 0
	                    ]);
	            }else {
	                $this->ajaxReturn([
	                    'msg' => '修改失败', 
	                    'status' => 1
	                    ]);
	            }
            }
            $this->ajaxReturn([
                'msg' => $this->news->getError(), 
                'status' => 1
                ]);
        }
	}

	/**
	 * 显示状态修改
	 * @return boolean [description]
	 */
	public function isStatus() {

		if (IS_GET) {
			$where['c_id'] = I('get.id');

			$status = $this->news->where($where)->getField('c_status');

			if ($status == 0) {
				$data['c_status'] = 1;
			} else {
				$data['c_status'] = 0;
			}

			if ($this->news->where($where)->save($data)) {
				$this->ajaxReturn([
                    'msg' => '修改成功', 
                    'status' => 0
                    ]);
			}
			$this->ajaxReturn([
                'msg' => '修改失败', 
                'status' => 1
                ]);
		}
	}

	/**
     * 删除
     */
    public function del(){

        if (IS_GET) {
        	$data['c_isdel'] = 1;
            if($this->news->where(['c_id' => I('get.id')])->save($data)) {
                $this->ajaxReturn(array(
                    'msg' => '删除成功！', 
                    'status' => 0
                    ));
            }else{
                $this->ajaxReturn(array(
                    'msg' => '删除失败！', 
                    'status' => 1
                    ));
            }
        }
    }
}




?>