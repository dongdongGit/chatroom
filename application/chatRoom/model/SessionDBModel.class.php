<?php
class SessionDBModel extends Model
{
    private static $table = 'session';
    private $result = null;

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
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    public function userSessionBegin()
    {
        // 初始化dao
        // require_once './config/config.php';
        // $this->_dao = new PDOMySQL();
        // 		echo 'Begin';
        parent::__construct();
    }

    public function userSessionEnd()
    {
        // 		echo 'End';
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
        $this->result = $this->_PDO->find(self::$table, 'session_content', "session_id='{$session_id}'");
        // 		echo 'Read';
        return (string)$this->result;
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
        $time = time();
        $data = [
                'session_id'      => $session_id,
                'session_content' => $session_content,
                'last_time'       => $time
        ];
        $this->result = $this->_PDO->replace($data, self::$table);
        // 		echo 'Write';
        // 		$sql = "REPLACE INTO session(session_id,session_content,last_time) VALUES('{$session_id}','{$session_content}',$time)";
        // 		$this->result = $this->_PDO->execute1($sql);
        print_r($this->result);
        // 		echo 'LastSQL:'.$this->_PDO->getLastSQL();
        return $this->result;
    }

    /**
     * 删除操作
     * 执行时间： 调用session_destroy()销毁session过程中被调用
     * 工作： 删除挡墙session的数据区(记录)
     * @param string $session_id
     * @return boolean
     */
    public function userSessionDelete($session_id)
    {
        $this->result = $this->_PDO->delete(self::$table, $session_id);
        // 		echo 'Delete';
        return $this->result;
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
        $this->result = $this->_PDO->delete(self::$table, "last_time<$time-'.$max_lifetime");
        // 		echo 'GC';
        return $this->result;
    }

    public function getSQL()
    {
        return $this->_PDO->getLastSQL();
    }
}
