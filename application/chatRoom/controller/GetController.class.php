<?php
class GetController extends CommentController
{
    private $uid = 0; // 用户uid
    private $count = 0; // 得到查询的总数
    private $lastResultMsg = null; // 上一次聊天的20条内容
    private $requestTime = 0; // 请求的时间
    private $nowTime = 0; // 现在的时间
    private $resultMsg = null; // 查询得到两次时间之间的内容

    /**
     * 用户请求服务器，服务器将发送两次请求内容时间段的内容(除请求用户(自己)外)响应给用户
     */
    public function getAction()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        // 		new SessionDB();
        // 		Factory::setModel('SessionDBModel');
        $this->uid = $_SESSION['uid'];
        $this->count = $this->commentModelPDO->total();
        if ($this->count === 0) {
            exit();
        }
        if ($this->userArray['msg'] == 'init') {
            $_SESSION['requesTime'] = time();
            $this->lastResultMsg = $this->commentModelPDO->byPubTimeDESC();
            $this->lastResultMsg = $this->dataFormat($this->lastResultMsg, $this->uid);
            $resultArr = [
                    'status' => '2',
                    'msg'    => $this->lastResultMsg
            ];
            echo json_encode($resultArr);
        } elseif (isset($this->userArray['msg'])) {
            // 			Factory::setModel('SessionDBModel');
            $this->requestTime = $_SESSION['requesTime'];
            $this->nowTime = time();
            $this->resultMsg = $this->commentModelPDO->byUidTimeFindPubTimeDESC($this->uid, $this->requestTime, $this->nowTime);
            if ($this->resultMsg) {
                $this->resultMsg = $this->dataFormat($this->resultMsg, $this->uid);
                $resultArr = [
                        'status' => '1',
                        'msg'    => $this->resultMsg
                ];
                echo json_encode($resultArr);
            }
            $_SESSION['requesTime'] = $this->nowTime;
        }
    }

    /**
     * 格式化发言信息，将ip转化成xxx.xxx.xxx.xxx的形式，已经过滤掉请求用户(自己)的发言内容
     * @param array $data
     * @param number $uid
     */
    public function dataFormat($data, $uid)
    {
        if (empty($data)) {
            return false;
        } // 是否为空，为空返回false
        if (!is_array($data)) {
            return false;
        } //是否为数组, 不是返回false
        if (count($data) != count($data, 1)) { //判断是否为一维数组即判断聊天内容是否只有一条，不是一维则进行多维数组的数据处理
            for ($i = 0; $i < count($data); $i++) {
                //获取第i项数组里的所有key值，以数组返回结果
// 				$key = array_keys($data[$i]);
                if ($data[$i]['uid'] === $uid) { //判断uid是否当前请求用户的uid，是则添加标志$date[$i]['flag'] = 1
                    $data[$i]['flag'] = 1;
                }
                $data[$i]['uip'] = long2ip($data[$i]['uip']);
                $data[$i]['pubTime'] = date('G:i:s', $data[$i]['pubTime']);
            }
        } else {
            if ($data['uid'] === $uid) {
                $data['flag'] = 1;
            }
            $data['uip'] = long2ip($data['uip']);
            $data['pubTime'] = date('G:i:s', $data['pubTime']);
        }
        return $data;
    }

    /**
     * 返回uid
     * @return number|unknown
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * 返回count
     * @return number|boolean
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * 返回查询结果集
     * @return unknown|boolean|array|string
     */
    public function getLastResultMsg()
    {
        return $this->lastResultMsg;
    }

    /**
     * 返回请求时的时间戳
     * @return number
     */
    public function getRequesTime()
    {
        return $this->requestTime;
    }

    /**
     * 返回现在时间戳
     * @return number
     */
    public function getNowTime()
    {
        return $this->nowTime;
    }

    /**
     * 返回处理后的数据
     * @return boolean|array|string|unknown
     */
    public function getResultMsg()
    {
        return $this->resultMsg;
    }
}
