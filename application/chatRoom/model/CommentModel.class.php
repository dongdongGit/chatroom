<?php
// require_once './framework/Model.class.php';

class CommentModel extends Model
{
    private static $table = 'comment';
    private $result = null;

    /**
     * 根据uid查找上一次发言时间
     * @param numeric $uid
     */
    public function byUidToPubTime($uid)
    {
        $this->result = $this->_PDO->find(self::$table, 'pubTime', 'uid=' . $uid, '', '', 'pubTime DESC', '0,1');
        return $this->result;
    }

    /**
     * 添加内容
     * @param array $data
     * @return boolean|number
     */
    public function addCommemt($data)
    {
        $this->result = $this->_PDO->add($data, self::$table);
        return $this->result;
    }

    /**
     * 根据用户uid查找用户信息
     * @return unknown
     */
    public function byUidFindComment()
    {
        $this->result = $this->_PDO->find(self::$table, 'uid,uip,username,content,pubTime');
        return $this->result;
    }

    /**
     * 返回以pubTime绛序的前20条记录
     * @return unknown
     */
    public function byPubTimeDESC()
    {
        $this->result = $this->_PDO->find(self::$table, 'uid,uip,username,content,pubTime', '', '', '', 'pubTime DESC', '0,20');
        return $this->result;
    }

    /**
     * 查找不等于uid并且pubTime在$requesTime与$nowTime之间的发言内容,以pubTime绛序
     * @param numeric $uid
     * @param numeric $requestTime
     * @param numeric $nowTime
     */
    public function byUidTimeFindPubTimeDESC($uid, $requestTime, $nowTime)
    {
        return $this->result = $this->_PDO->find(self::$table, 'uid,uip,username,content,pubTime', "uid<>{$uid} AND pubTime BETWEEN {$requestTime} AND {$nowTime}");
    }

    /**
     * 获得总数
     * @return boolean|number
     */
    public function total()
    {
        return $this->_PDO->total('cid', self::$table);
    }

    /**
     * 获得表名
     * @return string
     */
    public function getTable()
    {
        return self::$table;
    }

    /**
     * 获得上一次结果
     * @return unknown
     */
    public function getLastResult()
    {
        return $this->result;
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        $this->_PDO->close();
    }
}
