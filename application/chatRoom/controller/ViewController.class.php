<?php
class ViewController extends UserController
{
    /**
     * 载入用户登录页面
     */
    public function sLoginAction()
    {
        session_start();
        // Factory::setModel('SessionDBModel');
        // new SessionDB();
        // echo 'SESSION数据内容:';
        // var_dump($_SESSION);
        // session_write_close();
        if (isset($_SESSION['uid'])) {
            $this->_jump('/chatRoom/View/sChatMain');
        }
        require_once CURRENT_VIEW_PATH . 'login.html';
    }

    /**
     * 载入聊天室页面
     */
    public function sChatMainAction()
    {
        session_start();
        // Factory::setModel('SessionDBModel');
        // var_dump($test);
        // new SessionDB();
        // session_write_close();
        if (!isset($_SESSION['isLogin'])) {
            $this->_jump('/chatRoom/View/sLogin', '请先登录', 5);
        }
        if (!isset($_SESSION['uid']) && $_SESSION['isLogin'] != 'ok') {
            $this->_jump('/chatRoom/View/sLogin', '请先登录', 5);   
        }
        require_once CURRENT_VIEW_PATH . 'chatMain.html';
    }

    /**
     * 载入注册页面
     */
    public function sRegisterAction()
    {
        session_start();
        // Factory::setModel('SessionDBModel');
        // new SessionDB();
        // session_write_close();
        if (isset($_SESSION['uid'])) {
            $this->_jump('/chatRoom/View/sChatMain', '请先退出', 5);
        }
        require_once CURRENT_VIEW_PATH . 'register.html';
    }
}
