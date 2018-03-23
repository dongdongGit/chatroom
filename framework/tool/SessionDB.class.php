<?php
class SessionDB
{
    private $_dao;

    // private $_pdo;
    public function __construct()
    {
        // 设置session处理器
        ini_set('session.save_handler', 'user');
        session_set_save_handler(
            [$this, 'userSessionBegin'],
            [$this, 'userSessionEnd'],
            [$this, 'userSessionRead'],
            [$this, 'userSessionWrite'],
            [$this, 'userSessionDelete'],
            [$this, 'userSessionGC']
        );
        // 开启
        session_start();
    }

    public function userSessionBegin()
    {
        // 初始化dao
        // require_once CONFIG_PATH.'config.php';
        // 数据库信息组
        $config = [
            'hostname' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'database' => 'userdb',
            'hostport' => '3306',
            'dbms'     => 'mysql',
            'dsn'      => 'mysql' . ':host=1270.0.0.1;dbname=userdb'
        ];
        $this->_dao = new PDOMySQL($config);
        // echo '<br/>'.'开启';
        return true;
    }

    public function userSessionEnd()
    {
        // echo '<br/>'.'关闭';
        $this->_dao->close();
        return true;
    }

    /**
     * 读操作
     * 执行实际： session机制开启过程中执行
     * 工作： 从当前session数据区读取内容
     * @param string $session_id
     * @return string
     */
    public function userSessionRead($session_id)
    {
        // echo '<br/>'.'开始读取';
        // require CONFIG_PATH.'config.php';
        // $this->_dao = new PDOMySQL();
        $result = $this->_dao->find('session', 'session_content', "session_id='{$session_id}'");
        // print_r($result);
        // echo '<br/>';
        return (string)$result;
    }

    /**
     * 写操作
     * 执行时间： 脚本周期结束时，PHP在整理收尾时
     * 工作： 将当前脚本处理好的session数据，持久化存储到数据库中！
     * @param string $session_id
     * @param string $session_content
     * @return boolean
     */
    public function userSessionWrite($session_id, $session_content)
    {
        // require CONFIG_PATH.'config.php';
        // $this->_dao = new PDOMySQL();
        $time = time();
        $data = [
            session_id      => $session_id,
            session_content => $session_content,
            last_time       => $time
        ];
        $result = $this->_dao->replace($data, 'session');
        // $sql = "REPLACE INTO session VALUES ('$session_id', '$session_content', $time)";
        // $result = $this->_dao->execute($sql);
        // echo 'LastSQL:'.$this->_dao->getLastSQL();
        // print_r($result);
        // echo $this->_dao->getLastSQL();
        // echo '<br/>'.'写入';
        // echo $session_content;
        return $result;
    }

    /**
     * 删除操作
     * 执行时间： 调用session_destroy()销魂session过程中被调用
     * 工作： 删除挡墙session的数据区(记录)
     * @param string $session_id
     * @return boolean
     */
    public function userSessionDelete($session_id)
    {
        $result = $this->_dao->delete('session', "session_id='{$session_id}'");
        // echo '<br/>'.'删除';
        $_SESSION = [];
        return $result;
    }

    /**
     * 垃圾回收操作
     * 执行时机： 开启session时，有概率的执行
     * 工作： 删除那些过期的session数据区
     * @param int $max_lifetime
     * @return boolean
     */
    public function userSessionGC($max_lifetime)
    {
        $time = time();
        $result = $this->_dao->delete('session', "last_time<$time-'.$max_lifetime");
        // echo '<br/>'.'GC';
        return $result;
    }
}
