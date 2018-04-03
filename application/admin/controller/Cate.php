<?php
namespace app\admin\controller;

class Cate extends Base
{

    public function __construct() {

    	parent::__construct();
    	$this->cate = D("Cate");
    }
    /**
     * 首页渲染
     */
	public function index() {

		$count = $this->cate->count();
        #实例化think分页类
        $Page = new \Think\Page($count,15);
        $show       = $Page->show();// 分页显示输出
        $list = $this->cate->field('a.*,b.c_title as c_parent_title')
                ->alias('a')->join('LEFT JOIN __CATE__ b ON a.c_parent_id=b.c_id')
                ->order('c_sort,c_id asc')
                ->limit($Page->firstRow,$Page->listRows)->select();
		$this->assign([
			'title' => '分类列表',
			'list' => $list,
			'page' => $show
			]);
		$this->display();
	}
    /**
     * 渲染添加页面
     */
	public function add()
	{
        $cate_list = $this->cate->field('c_id,c_title,c_parent_id')->catetree();

		$this->assign([
			'title' => '添加分类',
			'cate_list' => $cate_list
			]);
		$this->display();
	}

		/**
	 * 执行添加
	 */
	public function do_add() {
        if (IS_POST) {
            //接收数据
            $data = array(
                'c_title' => I('post.c_title'),
                'c_pc_move' => I('post.c_pc_move'),
                'c_parent_id' => I('post.c_parent_id'),
                'c_nav_show' => I('post.c_nav_show'),
                'c_page_show' => I('post.c_page_show'),
                'c_move_icon' => I('post.c_move_icon'),
                'c_guihua' => I('post.c_guihua')
                );
            if ($this->cate->create($data)) {
            	if ($this->cate->add()) {
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
                'msg' => $this->cate->getError(), 
                'status' => 1
                ]);
        }
	}

	/**
	 * 修改页面渲染
	 */
	public function edit($id) {

		$list = $this->cate->where(['c_id' => $id])->find();
		$cate_list = $this->cate->field('c_id,c_title,c_parent_id')->where(['c_status' => 0])->catetree();

		$this->assign(array(
			'title' => '修改分类',
			'list' => $list,
			'cate_list' => $cate_list
			));
		$this->display();
	}

	/**
	 * 执行修改
	 */
	public function do_edit() {
        if (IS_POST) {
            //接收数据
            $data = array(
            	'c_id' => I('post.c_id'),
                'c_title' => I('post.c_title'),
                'c_pc_move' => I('post.c_pc_move'),
                'c_parent_id' => I('c_parent_id'),
                'c_nav_show' => I('post.c_nav_show'),
                'c_page_show' => I('post.c_page_show'),
                'c_move_icon' => I('post.c_move_icon'),
                'c_guihua' => I('post.c_guihua')
                );
            if ($this->cate->create($data)) {
            	if ($this->cate->save()) {
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
                'msg' => $this->cate->getError(), 
                'status' => 1
                ]);
        }
	}
    /**
	 * 排序
	 */
	public function sort() {

		foreach ($_POST as $k => $v) {
			$this->cate->where(array('c_id'=>$k))->setField('c_sort', $v);
		}
		header("location:{$_SERVER['HTTP_REFERER']}");
	}

	/**
	 * 显示状态修改
	 * @return boolean [description]
	 */
	public function isStatus() {

		if (IS_GET) {
			$where['c_id'] = I('get.id');

			$status = $this->cate->where($where)->getField('c_status');

			if ($status == 0) {
				$data['c_status'] = 1;
			} else {
				$data['c_status'] = 0;
			}

			if ($this->cate->where($where)->save($data)) {
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
            if($this->cate->where(['c_id' => I('id')])->delete()) {
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