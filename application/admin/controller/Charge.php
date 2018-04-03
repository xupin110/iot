<?php
namespace app\admin\controller;


class Charge extends Base
{
    public $charge;

    public function initialize() {

    	$this->charge = model("Charge");
    }
    /**
     * 首页渲染
     */
	public function index() 
    {
        $where = array('c_isdel' => 0);
        $list = $this->charge->getChargeList($where);
		return $this->fetch('',[
            'title' => '充电价格列表',
            'list' => $list,            
        ]);
	}
    /**
     * 渲染添加页面
     */
	public function add() {

		return $this->fetch('',[
            'title' => '添加充电价格'
        ]);
	}

	/**
	 * 执行添加
	 */
	public function doAdd() {
        if (request()->isPost()) 
        {
            //接收数据
            $data = [
                'c_charge_time' => input('post.c_charge_time'),
                'c_unit' => input('post.c_unit'),
                'c_price' => input('c_price'),
                'c_add_time' => time()
            ];
            if ($this->charge->save($data)) 
            {
                return json([
                        'msg' => '添加成功', 
                        'status' => 0
                       ]);
            }
            else
            {
                return json([
                        'msg' => '添加失败', 
                        'status' => 1                   
                       ]);
            }
        }
        else
        {
            return json([
                        'msg' => '添加失败', 
                        'status' => 1                 
                    ],'404');
        }
	}

	/**
	 * 修改页面渲染
	 */
	public function edit($id) {

		$list = $this->charge->where(['c_chargeid' => $id])->find();
        //var_dump($list);

        return $this->fetch('',[
            'title' => '修改充电价格',
            'list' => $list
        ]);
	}

	/**
	 * 执行修改
	 */
	public function doEdit() 
    {
        if (request()->isPost()) {
            //接收数据
            $data = [
            	'c_chargeid' => ( int ) input('post.c_chargeid'),
                'c_charge_time' => input('post.c_charge_time'),
                'c_unit' => input('post.c_unit'),
                'c_price' => input('c_price'),
            ];
            //validate 验证
            $res = $this->charge->save($data,['c_chargeid' => (int)input('post.c_chargeid')]);
            if($res){
                return json([
                        'msg' => '修改成功', 
                        'status' => 0
                ]);
            }else{
                return json([
                        'msg' => '修改失败', 
                        'status' => 1                   
                ]);
            }
	    }
    }
    

    /**
     * 状态修改
     */
    public function isStatus() {

        if (request()->isGet()) {
            $where['c_chargeid'] = ( int ) input('get.id');
            
            $status = ( int ) $this->charge->where($where)->value('c_status');
            if ($status == 0) {
                $data['c_status'] = 1;
            } else {
                $data['c_status'] = 0;
            }
            if ($this->charge->save($data,$where)) {
                return json([
                    'msg' => '修改成功', 
                    'status' => 0                    
                ]);
            }
            return json([
                'msg' => '修改失败', 
                'status' => 1
            ]);
        }
    }


	/**
     * 删除
     */
    public function del(){

        if (request()->isGet()) {
            $id = ( int ) input('get.id');
            if($this->charge->save(['c_isdel' => 1],['c_chargeid' => $id])) {
                return json([
                    'msg' => '删除成功！', 
                    'status' => 0
                ]);
            }else{          
                return json([
                    'msg' => '删除失败！', 
                    'status' => 1
                ]);
            }
        }

    }
}

