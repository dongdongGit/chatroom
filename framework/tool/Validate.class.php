<?php
class Validate
{
    private $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 自动调用方法
     * @param string $functionName
     * @param array $args
     */
    public static function __callStatic($functionName, $args)
    {
        if ($functionName == 'validate') {
            if ($args[1] == 'reg') {
                return self::validateRegister($args[0]);
            } elseif ($args[1] == 'login') {
                return self::validateLogin($args[0]);
            } elseif ($args[1] == 'active') {
                return self::validateActive($args[0]);
            } elseif ($args[1] == 'send') {
                return self::validateSendData($args[0]);
            } elseif ($args[1] == 'get') {
                return self::validateGetData($args[0]);
            }
        }
    }

    /**
     * 检测注册表单用户输入的数据是否符合要求
     * @param array $arr
     * @return boolean
     */
    public static function validateRegister(&$arr)
    {
        if (!($data['user_loginName'] = filter_input(INPUT_POST, 'userLgnName', FILTER_CALLBACK, [
            'options' => 'Validate::validateLoginName'
        ]))) {
            $errors['user_loginName'] = '请输入合法用户名';
        }
        if (!($data['username'] = filter_input(INPUT_POST, 'user', FILTER_CALLBACK, [
            'options' => 'Validate::validateUsername'
        ]))) {
            $errors['username'] = '请输入合法昵称';
        }
        if (!($data['password'] = filter_input(INPUT_POST, 'passwd', FILTER_CALLBACK, [
            'options' => 'Validate::validatePassword'
        ]))) {
            $errors['password'] = '请输入合法密码';
        }
        if (!($data['rePasswd'] = filter_input(INPUT_POST, 'rePasswd', FILTER_CALLBACK, [
            'options' => 'Validate::validateCheckPassword'
        ]))) {
            $errors['rePasswd'] = '请输入合法密码';
        } else {
            if ((strcmp($data['password'], $data['rePasswd']) != 0) || ($data['password'] == @$data['userLgnName'])) {
                $errors['rePasswd'] = '两次密码不一致或者密码不能与用户名相同';
            }
        }
        if (!($data['email'] = filter_input(INPUT_POST, 'email', FILTER_CALLBACK, [
            'options' => 'Validate::validateEmail'
        ]))) {
            $errors['email'] = '请输入合法邮箱';
        }
        if (!($data['mobliePhone'] = filter_input(INPUT_POST, 'mobliePhone', FILTER_CALLBACK, [
            'options' => 'Validate::validateMobliePhone'
        ]))) {
            $errors['mobliePhone'] = '请输入合法手机号码';
        }
        if (!empty($errors)) {
            $arr = $errors;
            return false;
        }
        $arr = $data;
        return true;
    }

    /**
     * 检测登录表单用户输入的数据是否符合要求
     * @param array $arr
     * @return boolean
     */
    public static function validateLogin(&$arr)
    {
        if (!($data['user_loginName'] = filter_input(INPUT_POST, 'userLgnName', FILTER_CALLBACK, [
            'options' => 'Validate::validateLoginName'
        ]))) {
            $errors['user_loginName'] = '请输入合法用户名';
        }
        if (!($data['password'] = filter_input(INPUT_POST, 'passwd', FILTER_CALLBACK, [
            'options' => 'Validate::validatePassword'
        ]))) {
            $errors['password'] = '请输入合法密码';
        }
        if (!($data['captcha'] = filter_input(INPUT_POST, 'captcha', FILTER_CALLBACK, [
            'options' => 'Validate::validateCaptcha'
        ]))) {
            $errors['captcha'] = '请输入合法验证码';
        }
        if (!empty($errors)) {
            $arr = $errors;
            return false;
        }
        $arr = $data;
        return true;
    }

    /**
     * 检验激活码是否合法
     * @param array $arr
     * @return boolean
     */
    public static function validateActive(&$arr)
    {
        if (!(@$data['token'] = filter_var($_GET['token'], FILTER_CALLBACK, [
            'options' => 'Validate::validateToken'
        ]))) {
            $errors['token'] = '请输入正确激活码';
        }
        if (!empty($errors)) {
            $arr = $errors;
            return false;
        }
        $arr = $data;
        return true;
    }

    /**
     * 校验内容是否合法
     * @param array $arr
     * @return boolean
     */
    public static function validateSendData(&$arr)
    {
        if (!($data['comment'] = filter_input(INPUT_POST, 'comment', FILTER_CALLBACK, [
            'options' => 'Validate::validateContent'
        ]))) {
            $errors['comment'] = '聊天内容长度不符合';
        }
        if (!empty($errors)) {
            $arr = $errors;
            return false;
        }
        $arr = $data;
        return true;
    }

    /**
     * 检验get格式是否正确
     * @param array $arr
     * @return boolean
     */
    public static function validateGetData(&$arr)
    {
        if (!($data['msg'] = filter_input(INPUT_GET, 'msg', FILTER_CALLBACK, [
            'options' => 'Validate::validateGetMsg'
        ]))) {
            $errors['msg'] = '获取错误';
        }
        if (!empty($errors)) {
            $arr = $errors;
            return false;
        }
        $arr = $data;
        return true;
    }

    /**
     * 检查内容是否为空,并转译html语言
     * @param string $str
     * @return boolean|string
     */
    public static function validateContent($str)
    {
        if (is_string($str)) {
            $strLength = mb_strlen($str, 'utf8');
            if (($strLength < 1 && $strLength > 260) || empty($str)) {
                return false;
            }
            $str = nl2br(htmlspecialchars($str, ENT_QUOTES));
        } else {
            return false;
        }
        return $str;
    }

    /**
     * 判断Msg的标志是否正确
     * @param string $str
     * @return boolean|unknown
     */
    public static function validateGetMsg($str)
    {
        if (!is_string($str) && !isset($str) && !(strcasecmp($str, 'init') != 0)) {
            return false;
        }
        return $str;
    }

    /**
     * 校验输入用户名是否符合要求
     *
     * @param string $str
     * @return boolean|string
     */
    public static function validateLoginName($str)
    {
        if (is_string($str)) {
            $regexTool = new RegexTool();
            $check = $regexTool->check('/^[a-zA-Z0-9_\.]+$/', $str);
            if (!(mb_strlen($str, 'utf8') >= 6 && mb_strlen($str, 'utf8') <= 16 && $check)) {
                return false;
            }
        } else {
            return false;
        }
        return $str;
    }

    /**
     * 校验输入昵称是否符合要求
     * @param string $str
     * @return boolean|string
     */
    public static function validateUsername($str)
    {
        if (is_string($str)) {
            $regexTool = new RegexTool();
            $check = $regexTool->check('/^[a-zA-Z0-9_\.\S]+$/', $str);
            if (!(mb_strlen($str, 'utf8') >= 2 && mb_strlen($str, 'utf8') <= 20 && $check)) {
                return false;
            }
        } else {
            return false;
        }
        return $str;
    }

    /**
     * 校验输入密码是否符合要求
     * @param unknown $str
     * @return boolean|string
     */
    public static function validatePassword($str)
    {
        if (is_string($str)) {
            $regexTool = new RegexTool();
            $check = $regexTool->check('/^([a-fA-F0-9]{32})$/', $str);
            if (!(mb_strlen($str, 'utf-8') == 32 && $check)) {
                return false;
            }
        } else {
            return false;
        }
        return $str;
    }

    /**
     * 检验验证码是否合法
     * @param string $str
     * @return boolean|unknown
     */
    public static function validateCaptcha($str)
    {
        if (is_string($str)) {
            $regexTool = new RegexTool();
            $check = $regexTool->check('/^[a-zA-Z0-9]/', $str);
            if (!(mb_strlen($str, 'utf-8') == CAPTCHA_LENGTH && $check)) {
                return false;
            }
        } else {
            return false;
        }
        return $str;
    }

    /**
     * 校验输入密码是否符合要求
     * @param string $str
     * @return boolean|string
     */
    public static function validateCheckPassword($str)
    {
        if (is_string($str)) {
            $regexTool = new RegexTool();
            $check = $regexTool->check('/^([a-fA-F0-9]{32})$/', $str);
            if (!(mb_strlen($str, 'utf-8') == 32 && $check)) {
                return false;
            }
        } else {
            return false;
        }
        return $str;
    }

    /**
     * 校验输入邮箱是否符合要求
     * @param string $str
     * @return boolean|string
     */
    public static function validateEmail($str)
    {
        if (is_string($str)) {
            $regexTool = new RegexTool();
            $check = $regexTool->check('/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/', $str);
            if (!(mb_strlen($str, 'utf8') >= 2 && mb_strlen($str, 'utf8') <= 30 && $check)) {
                return false;
            }
        } else {
            return false;
        }
        return $str;
    }

    /**
     * 校验输入手机号是否符合要求
     * @param string $str
     * @return boolean|string
     */
    public static function validateMobliePhone($str)
    {
        if (is_string($str)) {
            $regexTool = new RegexTool();
            $check = $regexTool->check('/^1[3,4,5,7,8]\d{9}$/', $str);
            if (!(mb_strlen($str, 'utf-8') == 11 && $check)) {
                return false;
            }
        } else {
            return false;
        }
        return $str;
    }

    /**
     * 检验激活码是否符合MD5格式要求
     * @param string $str
     * @return boolean|string
     */
    public static function validateToken($str)
    {
        if (is_string($str)) {
            $regexTool = new RegexTool();
            $check = $regexTool->check('/^([a-fA-F0-9]{32})$/', $str);
            if (!(mb_strlen($str, 'utf-8') == 32 && $check)) {
                return false;
            }
        } else {
            return false;
        }
        return $str;
    }
}
