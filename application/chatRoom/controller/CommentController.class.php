<?php
class CommentController extends Controller
{
    protected $result = null; // 数据验证的结果
    protected $userArray = []; // 用户发送的数据
    protected $userModelPDO = null; // userModelPDO对象
    protected $commentModelPDO = null; // CommentModelPDO对象

    /**
     * 重写父类的构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->__init();
    }

    /**
     * 初始化并对数据验证
     */
    protected function __init()
    {
        $act = ACTION; // 获取动作
        $this->result = parent::initValidate($act, $this->userArray); // 验证数据是否正确
        $this->userModelPDO = Factory::setModel('UserModel'); // 工厂类建立建立UserModel模型
        $this->commentModelPDO = Factory::setModel('CommentModel'); // 工厂类建立建立CommentModel模型
    }
}
