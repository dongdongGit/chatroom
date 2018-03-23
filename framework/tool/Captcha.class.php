<?php
class Captcha
{
    // 字体文件
    private $fontfile = '';
    // 字体大小
    private $size = 20;
    // 画布高度
    private $width = 120;
    // RGB值
    private $RGB = 0;
    // 画布宽度
    private $height = 34;
    // 验证码长度
    private $length = 4;
    // sin的最大值
    private $sinMax = 3;
    // 画布资源
    private $image = null;
    // 画布扭曲变形资源
    private $distortionImage = null;
    // 雪花个数
    private $snow = 0;
    // 像素个数
    private $pixel = 0;
    // 线段个数
    private $line = 0;
    // 是否扭曲变形标识
    private $distortionFlag = false;

    /**
     * 初始化数据配置
     * @param array $config
     * @return boolean|resource
     */
    public function __construct($config = [])
    {
        if (is_array($config) && count($config) > 0) {
            // 检测字体文件是否存在并且可读
            if (isset($config['fontfile']) && is_file($config['fontfile']) && is_readable($config['fontfile'])) {
                $this->fontfile = $config['fontfile'];
            } else {
                return false;
            }
            // 检测是否设置字体大小 检测是否设置画布高度和宽度 检测是否设置验证码长度 配置干扰元素
            foreach ($config as $key => $value) {
                if (isset($config[$key]) && $config[$key] > 0) {
                    $this->$key = (int)$config[$key];
                }
            }
            $this->image = imagecreatetruecolor($this->width, $this->height);
            return $this->image;
        } else {
            return false;
        }
    }

    /**
     * 绘制验证码
     * @return boolean
     */
    public function getCaptcha()
    {
        // 随机背景颜色
        $this->RGB = mt_rand(200, 255);
        $bgColor = imagecolorallocate($this->image, $this->RGB, $this->RGB, $this->RGB);
        // 填充矩形
        imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $bgColor);
        // 生成验证码
        $str = $this->generateStr($this->length);
        if (!$str) {
            return false;
        }
        // 绘制验证码
        $fontfile = $this->fontfile;
        for ($i = 0; $i < $this->length; $i++) {
            $size = mt_rand($this->size, $this->size + 8);
            $angle = mt_rand(-15, 15);
            $x = ceil($this->width / $this->length) * $i + mt_rand(5, 10);
            $y = ceil($this->height / 1.5);
            $color = $this->getRandColor();
            // text = mb_substr($str,$i,1,'utf-8'); 中文字符
            $text = $str{$i};
            imagettftext($this->image, $size, $angle, $x, $y, $color, $fontfile, $text);
        }
        // 绘制干扰元素
        if ($this->snow > 0) {
            $this->getSnow();
        } else {
            if ($this->pixel > 0) {
                $this->getPixel();
            }
            if ($this->line > 0) {
                $this->getLine();
            }
        }
        if ($this->distortionFlag) {
            $this->distortion();
        }
        // 输出图像
        $this->imageOutput();
        return $str;
    }

    /**
     * 产生验证码字符
     * @param number $length
     * @return boolean|string
     */
    private function generateStr($length = 4)
    {
        if ($length < 1 || $length > 30) {
            return false;
        }
        $chars = [
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'M', 'N', 'P', 'X', 'Y', 'Z', 1, 2, 3, 4, 5, 6, 7, 8, 9
        ];
        // 交换数组的键与值 随机取出数组$length个单元 以''分割连接成字符串
        $str = join('', array_rand(array_flip($chars), $length));
        return $str;
    }

    /**
     * 输出图片并关闭画布资源
     */
    private function imageOutput()
    {
        header('Content-Type:image/png');
        $this->distortionFlag ? imagepng($this->distortionImage) : imagepng($this->image);
        imagedestroy($this->image);
        imagedestroy($this->distortionImage);
    }

    /**
     * 产生雪花
     */
    private function getSnow()
    {
        for ($i = 0; $i < $this->snow; $i++) {
            imagestring($this->image, mt_rand(0, 5), mt_rand(0, $this->width), mt_rand(0, $this->height), '*', $this->getRandColor());
        }
    }

    /**
     * 绘制像素
     */
    private function getPixel()
    {
        for ($i = 0; $i < $this->pixel; $i++) {
            imagesetpixel($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), $this->getRandColor());
        }
    }

    /**
     * 绘制线段
     */
    private function getLine()
    {
        for ($i = 0; $i < $this->line; $i++) {
            imageline($this->image, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, $this->width), mt_rand(0, $this->height), $this->getRandColor());
        }
    }

    /**
     * 扭曲变形图片
     * @return resource
     */
    private function distortion()
    {
        $distortionImg = imagecreatetruecolor($this->width, $this->height);
        $bgColor = imagecolorallocate($distortionImg, $this->RGB, $this->RGB, $this->RGB);
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                // 获得(X,Y)坐标的颜色
                $pixelColor = imagecolorat($this->image, $x, $y);
                imagesetpixel($distortionImg, (int)($x + sin($y / $this->height * 2 * M_PI - M_PI * 0.5) * $this->sinMax), $y, $pixelColor);
            }
        }
        $this->distortionImage = imagecreatetruecolor($this->width, $this->height);
        imagefilledrectangle($this->distortionImage, 0, 0, $this->width, $this->height, $bgColor);
        imagecopy($this->distortionImage, $distortionImg, 2 * $this->sinMax, 0, $this->sinMax, 0, $this->width - 2 * $this->sinMax, $this->height);
        imagedestroy($distortionImg);
        return $this->distortionImage;
    }

    /**
     * 产生随机颜色
     * @return number
     */
    private function getRandColor()
    {
        return imagecolorallocate($this->image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
    }

    /**
     * 判断表单验证码是否正确
     * @param string $captcha
     * @return boolean
     */
    public function checkCaptcha($captcha = '')
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $result = isset($_SESSION['captcha']) && (strcasecmp($captcha, $_SESSION['captcha'])) == 0;
        unset($_SESSION['captcha']);
        return $result;
    }
}
