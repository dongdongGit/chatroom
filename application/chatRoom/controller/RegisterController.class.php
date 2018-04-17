<?php
class RegisterController extends UserController
{
    private $data = null;
    private $lastInsertId = null;
    private $userResult = null;

    /**
     * 注册功能
     */
    public function registerAction()
    {
        if ($this->result) {
            $user_loginName = $this->userArray['user_loginName'];
            $username = json_encode($this->userArray['username']);
            $password = md5($this->userArray['password'] . KEY);
            $rePasswd = md5($this->userArray['rePasswd'] . KEY);
            $email = $this->userArray['email'];
            $mobliePhone = $this->userArray['mobliePhone'];
            $regtime = time();
            $token = md5($user_loginName . $rePasswd . $regtime . KEY);
            $token_exptime = $regtime + 24 * 3600;
            $this->data = compact('user_loginName', 'username', 'password', 'email', 'mobliePhone', 'token', 'token_exptime', 'status', 'regtime');
            $this->userResult = $this->userModelPDO->addUser($this->data);
            $this->lastInsertId = $this->userModelPDO->getLastInsertId();
            if ($this->userResult) {
                $this->emailSend();
            } else {
                $result = [
                    'status' => '4', 'msg' => '注册失败,数据写入失败'
                ];
                echo json_encode($result);
                // $this->_jump('/application/chatRoom/wiew/register.php');
            }
        } else {
            echo '{"status":"0","errors":' . json_encode($this->userArray) . '}';
        }
        $this->userModelPDO->close();
    }

    /**
     * 发送邮件
     */
    public function emailSend()
    {
        require_once TOOL_PATH . 'swiftmailer-master/lib/swift_required.php';
        $transport = Swift_SmtpTransport::newInstance('smtp.qq.com', 465, 'ssl');
        $transport->setUsername('1697919363@qq.com')->setPassword('pfynhdwtnrbscahb');
        $mailer = Swift_Mailer::newInstance($transport);
        $message = Swift_Message::newInstance();

        $message->setFrom([
            '1697919363@qq.com' => 'Admin'
        ])->setTo([
            $this->data['email'] => 'User'
        ])->setSubject('骚表哥聊天室账号激活邮件');

        $url = 'http://' . $_SERVER['HTTP_HOST'] . "/chatRoom/Active/active?token={$this->data['token']}";
        // $urlencode = urlencode($url);
        $printStr = <<<EOF
				垃圾的{$this->data['user_loginName']}您好~！感谢您注册我们聊天室，您的昵称是{$this->data['username']}<br/>
                                请点击连接激活账号即可登陆！<br/>
                <a href="{$url}">{$url}</a><br/>
                                如果点此连接无反应,可以将其复制到浏览器中来执行,链接的有效时间为24小时。
EOF;
        $message->setBody("{$printStr}", 'text/html', 'utf-8');
        $mailer->protocol = 'stmp';

        try {
            if ($mailer->send($message)) {
                $resultReg = [
                    'status' => '1', 'msg' => '注册成功', 'url' => '/chatRoom/View/sLogin'
                ];
                echo json_encode($resultReg);
            } else {
                $this->userModelPDO->deleteUser($this->lastInsertId);
                $resultReg = [
                    'status' => '2', 'msg' => '注册失败，邮件发送失败'
                ];
                echo json_encode($resultReg);
            }
        } catch (Swift_SwiftException $e) {
            // echo '邮件发送错误' . $e->getMessage();
            $result = [
                'status' => '3', 'msg' => '邮件发送错误:' . $e->getMessage()
            ];
            echo json_encode($result);
        }
    }

    /**
     * 检测用户名是否存在并json数组输出结果
     */
    public function lgnExistsAction()
    {
        $userLgnName = filter_input(INPUT_POST, 'userLgnName');
        if (Validate::validateLoginName($userLgnName)) {
            $this->userResult = $this->userModelPDO->userLoginNameAlreadyExists($userLgnName);
            $this->userResult ? $isAvailable = false : $isAvailable = true;
            echo json_encode([
                'valid' => $isAvailable
            ]);
        }
    }

    /**
     * 检测昵称是否存在并json数组输出结果
     */
    public function nameExistsAction()
    {
        $username = filter_input(INPUT_POST, 'user');
        if (Validate::validateUsername($username)) {
            $this->userResult = $this->userModelPDO->nameAlreadyExists($username);
            $this->userResult ? $isAvailable = false : $isAvailable = true;
            echo json_encode([
                'valid' => $isAvailable
            ]);
        }
    }

    /**
     * 检测手机号码是否注册并json数组输出结果
     */
    public function moblieExistsAction()
    {
        $mobliePhone = filter_input(INPUT_POST, 'mobliePhone');
        if (Validate::validateLoginName($mobliePhone)) {
            $this->userResult = $this->userModelPDO->mobliePhoneRegistered($mobliePhone);
            $this->userResult ? $isAvailable = false : $isAvailable = true;
            echo json_encode([
                'valid' => $isAvailable
            ]);
        }
    }

    /**
     * 获取userArray数据
     */
    public function getUserArray()
    {
        return $this->userArray;
    }

    /**
     * 获取上一条数据ID
     */
    public function getLastInsertId()
    {
        return $this->lastInsertId;
    }
}
