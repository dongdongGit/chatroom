<?php
// require_once './framework/Model.class.php';
class UserModel extends Model
{
    private static $table = 'user';
    private $result = null;

    /**
     * 获得查询的表名
     * @return the $table
     */
    public function getTable()
    {
        return self::$table;
    }

    /**
     * 查找用户
     * @param string $user_loginName
     * @param string $password
     * @return unknown
     */
    public function findUser($user_loginName, $password)
    {
        $this->result = $this->_PDO->find(self::$table, 'uid,status', "user_loginName='{$user_loginName}' AND password='{$password}'");
        return $this->result;
    }

    /**
     * 查找未激活用户
     * @param string|array $fields
     * @param string $token
     * @return unknown
     */
    public function findNotActiveUser($token)
    {
        $this->result = $this->_PDO->find(self::$table, ['uid', 'token_exptime'], "token='{$token}' AND status=0");
        return $this->result;
    }

    /**
     * 添加用户
     * @param array $data
     * @return boolean|number
     */
    public function addUser($data)
    {
        $this->result = $this->_PDO->add($data, self::$table);
        return $this->result;
    }

    /**
     * 修改用户状态，修改为激活
     * @param array $data
     * @return boolean|number
     */
    public function updataUserStatusToActivate($uid)
    {
        $this->result = $this->_PDO->update(['status' => 1], self::$table, 'uid=' . $uid);
        return $this->result;
    }

    /**
     * 删除上一条插入的记录
     * @param string $lastInsertId
     * @return boolean|number
     */
    public function deleteUser($lastInsertId)
    {
        $this->result = $this->_PDO->delete(self::$table, 'uid=' . $lastInsertId);
        return $this->result;
    }

    /**
     * 删除激活时间过期用户
     * @param string $token
     * @return boolean|number
     */
    public function deleteActiontionTimeExpiredUser($token)
    {
        $this->result = $this->_PDO->delete(self::$table, "token='{$token}'");
        return $this->result;
    }

    /**
     * 根据uid查找username,uip
     * @param numeric $uid
     */
    public function byUidFindUsernameUip($uid)
    {
        $this->result = $this->_PDO->find(self::$table, 'username,uip', "uid={$uid}");
        return $this->result;
    }

    /**
     * 根据uid查找用户并修改用户uip
     * @param number $ip
     * @param number $uid
     * @return boolean|number
     */
    public function byUidUpdateUip($ip, $uid)
    {
        $this->result = $this->_PDO->update(['uip' => $ip], self::$table, 'uid=' . $uid);
        return $this->result;
    }

    /**
     * 检测用户名是否存在
     * @param string $user_loginName
     * @return boolean
     */
    public function userLoginNameAlreadyExists($user_loginName)
    {
        $this->result = $this->_PDO->find(self::$table, 'user_loginName', "user_loginName='{$user_loginName}'");
        return $this->result;
    }

    /**
     * 检测昵称是否存在
     * @param string $username
     * @return boolean
     */
    public function nameAlreadyExists($username)
    {
        $this->result = $this->_PDO->find(self::$table, 'username', "username='{$username}'");
        return $this->result;
    }

    /**
     * 检测手机号码是否已经被注册
     * @param string $mobliePhone
     * @return boolean|number
     */
    public function mobliePhoneRegistered($mobliePhone)
    {
        $this->result = $this->_PDO->find(self::$table, 'mobliePhone', "mobliePhone='{$mobliePhone}'");
        return $this->result;
    }

    /**
     * 获得上一条添加记录的ID
     * @return boolean|string
     */
    public function getLastInsertId()
    {
        return $this->_PDO->getLastInsertId();
    }

    /**
     * 获得上一次结果
     * @return string
     */
    public function getLastResult()
    {
        return $this->result;
    }

    /**
     * 获得最后一次执行的SQL语句
     * @return boolean|string
     */
    public function getLastSQL()
    {
        return $this->_PDO->getLastSQL();
    }

    /**
     * 关闭数据库
     */
    public function close()
    {
        return $this->_PDO->close();
    }
}
