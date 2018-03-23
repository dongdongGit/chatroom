<?php
class SendController extends CommentController
{
    private $uid = 0; // 获取uid值
    private $resTime = 0; // 获得查询返回的时间
    private $pubTime = 0; // 获得发送的时间
    private $comment = null; // 获得内容
    private $usernameUipResult = null;
    // 根据uid查找username、uip的信息结果

    /**
     * 获得上一次发言的时间
     */
    public function lastByUidPubTime()
    {
        // new SessionDBModel();
        session_start();
        // new SessionDB();
        $this->pubTime = time();
        $this->uid = $_SESSION['uid'];
        $this->resTime = $this->commentModelPDO->byUidToPubTime($this->uid);
    }

    /**
     * 相邻两次发言时间
     */
    public function cooled()
    {
        if ($this->resTime && ($this->pubTime - $this->resTime['pubTime'] < 1)) {
            $msgTime = [
                'status' => '1',
                'msg'    => '发送消息过快休息一会吧!'
            ];
            echo json_encode($msgTime);
            die();
        }
    }

    /**
     * 添加用户信息到数据库并发送给用户
     */
    public function addUserMsgAndSendMsg()
    {
        $this->comment = $this->userArray['comment'];
        $this->usernameUipResult = $this->userModelPDO->byUidFindUsernameUip($this->uid);
        $this->comment = [
            'uid'      => $this->uid,
            'uip'      => $this->usernameUipResult['uip'],
            'username' => $this->usernameUipResult['username'],
            'content'  => $this->comment,
            'pubTime'  => $this->pubTime
        ];
        $result = $this->commentModelPDO->addCommemt($this->comment);
        $this->comment['uip'] = long2ip($this->usernameUipResult['uip']);
        $this->comment['pubTime'] = date('G:i:s', $this->pubTime);
        unset($this->comment['uid']);
        if ($result) {
            $msgComment = [
                'status' => '2',
                'msg'    => $this->comment
            ];
            echo json_encode($msgComment);
        }
    }

    /**
     * 程序入口
     */
    public function sendAction()
    {
        if ($this->result) {
            $this->lastByUidPubTime();
            $this->cooled();
            $this->addUserMsgAndSendMsg();
        } else {
            echo '{"status":"0","errors":' . json_encode($this->result) . '}';
        }
        $this->commentModelPDO->close();
        $this->userModelPDO->close();
    }

    /**
     * 返回用户id
     * @return the $uid
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * 返回请求时间
     * @return the $resTime
     */
    public function getResTime()
    {
        return $this->resTime;
    }

    /**
     * 返回上一次发言时间
     * @return the $pubTime
     */
    public function getPubTime()
    {
        return $this->pubTime;
    }

    /**
     * 返回发言内容
     * @return the $comment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * 返回结果
     * @return the $usernameUipResult
     */
    public function getUsernameUipResult()
    {
        return $this->usernameUipResult;
    }
}
