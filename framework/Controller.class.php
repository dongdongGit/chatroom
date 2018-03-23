<?php
class Controller
{
    public $vali = null;

    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->__initContentType();
    }

    /**
     * 初始化Content-Type
     */
    protected function __initContentType()
    {
        header('Content-Type:text/html;charset=utf-8');
    }

    /**
     * 验证表单数据是否合法
     */
    public function initValidate($act, &$array)
    {
        if ($act == 'login') {
            $this->vali = Validate::validateLogin($array);
            return $this->vali;
        } elseif ($act == 'register') {
            $this->vali = Validate::validateRegister($array);
            return $this->vali;
        } elseif ($act == 'active') {
            $this->vali = Validate::validateActive($array);
            return $this->vali;
        } elseif ($act == 'send') {
            $this->vali = Validate::validateSendData($array);
            return $this->vali;
        } elseif ($act == 'get') {
            $this->vali = Validate::validateGetData($array);
        }
    }

    /**
     * 跳转页面
     * @param string $url
     * @param string $info
     * @param number $wait
     */
    protected function _jump($url, $info = null, $wait = 3)
    {
        if (is_null($info)) {
            header('Location:' . $url);
        } else {
            header("Refresh:$wait;url=$url");
            echo $info;
        }
        die();
    }
}
