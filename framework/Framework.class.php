<?php
/**
 * 框架初始化功能类
 * @author Administrator
 *
 */
class Framework
{
    public static function run()
    {
        // 声明路径常量
        static::_initPathConst();
        // 初始化配置
        static::_initConfig();
        // 初始化数据库连接配置
        static::_initDatabaseConfig();
        // 确定分发参数
        static::_initDispatchParam();
        // 当前平台相关的路径常量
        static::_initPlatformPathParam();
        // 注册自动加载
        static::_initAutoload();
        // 请求分发
        static::_Dispatch();
    }

    /**
     * 声明路径常量
     */
    private static function _initPathConst()
    {
        // 目录常量的定义
        define('ROOT_PATH', getcwd() . '/'); // getCWD()获得当前目录
        define('APPLICATION_PATH', ROOT_PATH . 'application/');
        define('CONFIG_PATH', APPLICATION_PATH . 'config/');
        define('FRAMEWORK_PATH', ROOT_PATH . 'framework/');
        define('TOOL_PATH', FRAMEWORK_PATH . 'tool/');
        define('PUBLIC', ROOT_PATH . 'public/');
    }

    /**
     * 初始化配置
     */
    private static function _initConfig()
    {
        // 存储于全局变量中，可以在整个项目中可以使用该配置数据
        $GLOBALS['config'] = require CONFIG_PATH . 'chatRoom.config.php';
        // $timezone = $GLOBALS['config']['info']['DEFAULT_TIMEZONE'];
        $default_key = $GLOBALS['config']['info']['DEFAULT_KEY'];
        $default_captcha_length = $GLOBALS['config']['captcha']['length'];
        // define('DATE_DEFAULT_TIMEZONE', $timezone);
        define('KEY', $default_key);
        define('CAPTCHA_LENGTH', isset($default_captcha_length) ? $default_captcha_length : 4);
        // date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
    }

    /**
     * 若要重新新的配置
     * array(
     * 'hostname' => 'DB_HOST',
     * 'username' => 'DB_USER',
     * 'password' => 'DB_PWD',
     * 'database' => 'DB_NAME',
     * 'hostport' => 'DB_PORT',
     * 'dbms' => 'DB_TYPE',
     * 'dsn' => 'DB_TYPE' . ":host=" . 'DB_HOST' . ";dbname=" . 'DB_NAME',
     * )
     * 初始化数据库连接配置
     */
    private static function _initDatabaseConfig()
    {
        $default_host = $GLOBALS['config']['db']['HOST'];
        define('DB_HOST', $default_host);
        $default_user = $GLOBALS['config']['db']['USER'];
        define('DB_USER', $default_user);
        $default_password = $GLOBALS['config']['db']['PWD'];
        define('DB_PWD', $default_password);
        $default_name = $GLOBALS['config']['db']['NAME'];
        define('DB_NAME', $default_name);
        $default_type = $GLOBALS['config']['db']['TYPE'];
        define('DB_TYPE', $default_type);
        $default_port = $GLOBALS['config']['db']['PORT'];
        define('DB_PORT', $default_port);
        $default_charset = $GLOBALS['config']['db']['CHARSET'];
        define('DB_CHARSET', $default_type);
    }

    /**
     * 初始化分发参数
     */
    private static function _initDispatchParam()
    {
        // 确定分发参数
        // 平台
        $default_platform = $GLOBALS['config']['app']['DEFAULT_PLATFORM'];
        define('PLATFORM', isset($_GET['p']) ? $_GET['p'] : $default_platform);
        // 控制器类
        $default_controller = $GLOBALS['config'][PLATFORM]['DEFAULT_CONTROLLER']; // 当前平台的默认控制器
        define('CONTROLLER', isset($_GET['c']) ? $_GET['c'] : $default_controller);
        // 动作
        $default_action = $GLOBALS['config'][PLATFORM]['DEFAULT_ACTION'];
        define('ACTION', isset($_GET['a']) ? $_GET['a'] : $default_action);
    }

    /**
     * 声明当前平台路径常量
     */
    private static function _initPlatformPathParam()
    {
        // 当前平台相关的路径常量
        define('CURRENT_CONTROLLER_PATH', APPLICATION_PATH . PLATFORM . '/controller/');
        define('CURRENT_MODEL_PATH', APPLICATION_PATH . PLATFORM . '/model/');
        define('CURRENT_VIEW_PATH', APPLICATION_PATH . PLATFORM . '/view/');
    }

    /**
     * 自动加载方法
     */
    public static function userAutoload($class_name)
    {
        // 先处理确定的（框架中的核心类）
        // 类名与类文件映射数组
        $framework_class_list = [
            'Controller' => FRAMEWORK_PATH . 'Controller.class.php', 'Model' => FRAMEWORK_PATH . 'Model.class.php', 'Factory' => FRAMEWORK_PATH . 'Factory.class.php', 'PDOMySQL' => FRAMEWORK_PATH . 'PDOMySQL.class.php', 'Validate' => TOOL_PATH . 'Validate.class.php',
            'RegexTool'  => TOOL_PATH . 'RegexTool.class.php', 'SessionDB' => TOOL_PATH . 'SessionDB.class.php', 'Captcha' => TOOL_PATH . 'Captcha.class.php'
        ];
        // 判断是否为核心类
        if (isset($framework_class_list[$class_name])) {
            require $framework_class_list[$class_name];
        } elseif (substr($class_name, -10) == 'Controller') { // 判断是否为可增加（控制器类，模型类）|控制器类，截取后是个字符，匹配Controller
            require CURRENT_CONTROLLER_PATH . $class_name . '.class.php';
        } elseif (substr($class_name, -5) == 'Model') { // 模型类，截取后5个字符，匹配Model|模型类，当前平台下model目录
            require CURRENT_MODEL_PATH . $class_name . '.class.php';
        }
    }

    /**
     * 注册自动加载
     */
    private static function _initAutoload()
    {
        spl_autoload_register([
            __CLASS__, 'userAutoload'
        ]);
    }

    /**
     * 分发请求
     */
    private static function _Dispatch()
    {
        $controller_name = CONTROLLER . 'Controller';
        // 实例化
        $controller = new $controller_name(); // 可变类|调用方法(action动作)| 拼凑当前的方法动作名字符串
        $action_name = ACTION . 'Action';
        $controller->$action_name();
    }
}
