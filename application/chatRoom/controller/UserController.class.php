<?php
class UserController extends Controller
{
    protected $result = null;
    protected $userArray = [];
    protected $userModelPDO = null;

    /**
     * 重写父类的构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->__initValidationDateMode();
    }

    /**
     * 初始化并对数据验证
     */
    protected function __initValidationDateMode()
    {
        $act = ACTION;
        $this->result = parent::initValidate($act, $this->userArray);
        $this->userModelPDO = Factory::setModel('UserModel');
    }
}
