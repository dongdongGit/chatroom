<?php
class ActiveController extends UserController
{
    private $token = null; // 激活码
    private $row = null; // 查询结果
    private $now = 0; // 激活时间

    /**
     * 用户激活
     */
    public function activeAction()
    {
        if ($this->result) {
            $this->token = addslashes($this->userArray['token']);
            $this->row = $this->userModelPDO->findNotActiveUser($this->token);
            $this->now = time();
            if ($this->row) {
                $this->findActiveInfo();
            } else {
                echo '账号已激活或激活码错误';
            }
        } else {
            echo '{"status":"0","errors":' . json_encode($this->userArray) . '}';
        }
        $this->userModelPDO->close();
    }

    /**
     * 激活成功，修改用户状态
     */
    public function findActiveInfo()
    {
        if ($this->now > $this->row['token_exptime']) {
            $this->userModelPDO->deleteActiontionTimeExpiredUser($this->token);
            echo '激活时间过期，请重新注册';
        } else {
            $res = $this->userModelPDO->updataUserStatusToActivate($this->row['uid']);
            if ($res) {
                echo '激活成功，3秒后跳转到登陆界面';
                echo '<meta http-equiv="refresh" content="3;url=./index.php" />';
            } else {
                echo '激活失败，你失败了';
                echo '<meta http-equiv="refresh" content="3;url=./index.php"/>';
            }
        }
    }

    /**
     * 返回激活码信息
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * 返回查询结果
     * @return unknown
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * 返回激活时间
     * @return number
     */
    public function getNow()
    {
        return $this->now;
    }
}
