<?php
class LoginController extends UserController
{
    private $ip = 0;
    private $row = null;

    /**
     * 用户登录表单，进行验证
     */
    public function loginAction()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        // if (!isset($_SESSION)) {
        //     new SessionDB();
        // }
        // 验证表单数据是否合法
        if ($this->result) {
            // 获取登录表单信息
            $user_loginName = $this->userArray['user_loginName'];
            $password = md5($this->userArray['password'] . KEY);
            $captcha_str = $this->userArray['captcha'];
            // 判断验证码是否正确
            $captcha = new Captcha();
            $checkCaptcha = $captcha->checkCaptcha($captcha_str);
            if (!$checkCaptcha) {
                $result = [
                    'status' => '1',
                    'msg'    => '验证码错误'
                ];
                echo json_encode($result);
                die();
            }
            // 判断用户账号密码是否正确
            $this->row = $this->userModelPDO->findUser($user_loginName, $password);
            if (!$this->row) {
                $result = [
                    'status' => '1',
                    'msg'    => '账号或密码错误'
                ];
                return json_encode($result);
            } else {
                // 判断用户账号是否激活
                if ($this->row['status'] == 0) {
                    $result = [
                        'status' => '1',
                        'msg'    => '账号未激活，请去注册时填写的邮箱激活()'
                    ];
                    echo json_encode($result);
                } else {
                    // if($_POST['rememberPassword'] == 1) {
                    // setcookie("user_loginName", filter_input(INPUT_POST, 'userLgnName'), time() + 24 * 3600 * 7, '/', '', false, true);
                    // setcookie("password", filter_input(INPUT_POST, 'passwd'), time() + 24 * 3600 * 7, '/', '', false, true);
                    // }
                    $this->ip = bindec(decbin(ip2long($this->getIPaddress())));
                    $this->userModelPDO->byUidUpdateUip($this->ip, $this->row['uid']);
                    // $test = Factory::setModel('SessionDBModel');
                    $_SESSION['uid'] = $this->row['uid'];
                    $_SESSION['isLogin'] = 'ok';
                    $result = [
                        'status' => '2',
                        'msg'    => '登录成功马上跳转()',
                        'url'    => '/chatRoom=/View/sChatMain'
                    ];
                    echo json_encode($result);
                }
            }
        } else {
            echo '{"status":"0","errors":' . json_encode($this->userArray) . '}';
        }
        $this->userModelPDO->close();
    }

    /**
     * 生成登录界面的验证码动作，添加到img标签src属性
     */
    public function captchaAction()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        // 		if(!isset($_SESSION)) {
        // 			new SessionDB();
        // 		}
        $config = $GLOBALS['config']['captcha'];
        // 利用Captcha工具类绘制验证码
        $captcha = new Captcha($config);
        $_SESSION['captcha'] = $captcha->getCaptcha();
    }

    /**
     * 判断验证码是否正确
     */
    public function captchaExistsAction()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        // 		if(!isset($_SESSION)) {
        // 			new SessionDB();
        // 		}
        $captchaCode = filter_input(INPUT_POST, 'captcha');
        if (Validate::validateCaptcha($captchaCode)) {
            $isAvailable = isset($_SESSION['captcha']) && (strcasecmp($captchaCode, $_SESSION['captcha']) == 0);
            echo json_encode([
                'valid' => $isAvailable
            ]);
        }
    }

    /**
     * 获取IP地址
     */
    public function getIPaddress()
    {
        $IPaddress = '';
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $IPaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $IPaddress = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $IPaddress = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $IPaddress = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $IPaddress = getenv('HTTP_CLIENT_IP');
            } else {
                $IPaddress = getenv('REMOTE_ADDR');
            }
        }
        return $IPaddress;
    }

    /**
     * 返回用户IP
     *
     * @return number
     */
    public function getUserIP()
    {
        return $this->ip;
    }

    /**
     * 返回查询结果集
     *
     * @return the $row
     */
    public function getRow()
    {
        return $this->row;
    }
}
