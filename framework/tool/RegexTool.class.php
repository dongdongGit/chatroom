<?php
class RegexTool
{
    // 常用正则表达式变量池
    private $validate = [
        'require'  => '/.+/', 'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
        'url'      => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
        'currency' => '/^\d+(\.\d+)?$/',
        'number'   => '/^\d+$/',
        'zip'      => '/^\d{6}$/',
        'integer'  => '/^[-\+]?\d+$/',
        'double'   => '/^[-\+]?\d+(\.\d+)?$/',
        'english'  => '/^[A-Za-z]+$/',
        'qq'       => '/^\d{5,11}$/',
        'mobile'   => '/^1(3|4|5|7|8)\d{9}$/'
    ];
    // 返回匹配结果集(不确定)
    private $returnMatchResult = false;
    // 修正模式
    private $fixMode = null;
    // 存储到匹配的字符串
    private $matches = [];
    // 是否有匹配到
    private $isMatch = false;

    /**
     * 设置正则表达式、修正模式
     * @param string $returnMatchResult
     * @param unknown $fixMode
     */
    public function __construct($returnMatchResult = false, $fixMode = null)
    {
        $this->returnMatchResult = $returnMatchResult;
        $this->fixMode = $fixMode;
    }

    /**
     * regex核心
     * @param unknown $pattern
     * @param unknown $subject
     * @return boolean
     */
    private function regex($pattern, $subject)
    {
        // 是否正则表达式常量池中有常量
        if (array_key_exists(strtolower($pattern), $this->validate)) {
            $pattern = $this->validate[$pattern] . $this->fixMode;
        }
        $this->returnMatchResult ? preg_match_all($pattern, $subject, $this->matches) : $this->isMatch = preg_match($pattern, $subject) === 1;
        return $this->getRegexResult();
    }

    /**
     * 返回结果
     * @return boolean
     */
    private function getRegexResult()
    {
        if ($this->returnMatchResult) {
            return $this->matches;
        } else {
            return $this->isMatch;
        }
    }

    public function toggleReturnType($bool = null)
    {
        if (empty($bool)) {
            $this->returnMatchResult = !$this->returnMatchResult;
        } else {
            $this->returnMatchResult = is_bool($bool) ? $bool : (bool)$bool;
        }
    }

    /**
     * 设置修正模式
     * @param unknown $fixMode
     */
    public function setFixMode($fixMode)
    {
        $this->fixMode = $fixMode;
    }

    public function notEmpty($str)
    {
        return $this->regex('require', $str);
    }

    public function isEmail($email)
    {
        return $this->regex('email', $email);
    }

    public function isMobile($mobile)
    {
        return $this->regex('mobile', $mobile);
    }

    public function check($pattern, $subject)
    {
        return $this->regex($pattern, $subject);
    }

    // ......
}
