<?php
class PDOMySQL
{
    private static $config = [];
    // 设置连接参数的配置信息
    private static $link = null;
    // 保存连接标识符
    private static $pconnect = false;
    // 是否开启长连接
    private static $dbVersion = null;
    // 保存数据库版本号
    private static $connected = false;
    // 是否连接成功
    private static $queryStr = null;
    // 保存sql语句
    private static $PDOStatement = null;
    // 保存PDOStatement对象
    private static $error = null;
    // 保存错误信息
    private static $lastInsertId = null;
    // 保存上一步插入操作产生AUTO_INCREMENT
    private static $numRows = 0;
    // 上一步操作产生受影响的记录的条数

    /**
     * 连接PDO
     * @param string $dbConfig
     * @return boolean
     */
    public function __construct($dbConfig = '')
    {
        if (!class_exists('PDO')) {
            self::throw_exception('不支持PDO,请先开启');
        }
        // 检测$dbConfig 是否为数组。 是对数组进行赋值
        if (!is_array($dbConfig)) {
            $dbConfig = [
                'hostname' => DB_HOST,
                'username' => DB_USER,
                'password' => DB_PWD,
                'database' => DB_NAME,
                'hostport' => DB_PORT,
                'dbms'     => DB_TYPE,
                'dsn'      => DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME
            ];
        }
        // 检测$dbConfig['hostname']是否配置,否抛出异常信息
        if (empty($dbConfig['hostname'])) {
            self::throw_exception('请先配置数据库信息');
        }
        self::$config = $dbConfig;
        if (empty(self::$config['params'])) {
            self::$config['params'] = [];
        }
        if (!isset(self::$link)) {
            $configs = self::$config;
            if (self::$pconnect) {
                // echo self::$pconnect;
                // 开启长连接，添加到配置数组中
                $configs['params'][constant('PDO::ATTR_PERSISTENT')] = true;
            }
            try {
                // 建立PDO的连接
                self::$link = new PDO($configs['dsn'], $configs['username'], $configs['password'], $configs['params']);
            } catch (PDOException $e) {
                self::throw_exception($e->getMessage());
            }
            if (!self::$link) {
                self::throw_exception('PDO连接错误');
                return false;
            }
            // 编码方式
            self::$link->exec('SET NAMES ' . DB_CHARSET);
            self::$dbVersion = self::$link->getAttribute(constant('PDO::ATTR_SERVER_VERSION'));
            self::$connected = true;
            // 销毁$Configs
            unset($configs);
        }
    }

    public static function getInstance()
    {
        if (!isset(self::$link)) {
            self::$link = new self();
        }
        return self::$link;
    }

    /**
     * 得到所有所有记录
     * @param string $sql
     */
    public static function getAll($sql = null)
    {
        // 检测$sql是否为null 是不进行操作,不是则执行query($sql)。
        if ($sql != null) {
            self::query($sql);
        }
        // 得到查询返回的结果集，数组以关联数组的方式获取
        $result = self::$PDOStatement->fetchAll(constant('PDO::FETCH_ASSOC'));
        return $result;
    }

    /**
     * 得到结果集中的一条记录
     * @param string $sql
     * @return mixed
     */
    public static function getRow($sql = null)
    {
        if ($sql != null) {
            self::$query($sql);
        }
        $result = self::$PDOStatement->fetch(constant('PDO::FETCH_ASSOC'));
        return $result;
    }

    /**
     * 根据主键查找记录 要查找的字段必须是数字
     * @param string $table
     * @param string $priId
     * @param string $fields
     * @return mixed
     */
    public static function findById($table, $priId, $fields = '*')
    {
        // sprintf格式化输出
        $sql = 'SELECT %s FROM %s WHERE uid=%d';
        return self::getRow(sprintf($sql, self::parseFields($fields), $table, $priId));
    }

    /**
     * 查询语句
     * @param string $table
     * @param string $fields
     * @param string $where
     * @param string $group
     * @param string $having
     * @param string $order
     * @param string $limit
     * @return unknown
     */
    public static function find($table, $fields = '*', $where = null, $group = null, $having = null, $order = null, $limit = null)
    {
        // 拼接sql语句
        $sql = 'SELECT ' . self::parseFields($fields) . ' FROM ' . $table . self::parseWhere($where) . self::parseGroup($group) . self::parseHaving($having) . self::parseOrder($order) . self::parseLimit($limit);
        // 执行sql语句
        $dataAll = self::getAll($sql);
        // 假如只有一条只返回dataAll[0]
        return count($dataAll) == 1 ? $dataAll[0] : $dataAll;
    }

    /**
     * 添加一条数据
     * @param array $data
     * @param string $table
     * @return boolean|number
     */
    public static function add($data, $table)
    {
        $keys = array_keys($data); // array_keys获得数组的键
        $values = array_values($data); // array_values获得数组的值
        array_walk($keys, [ // array_walk对数组每一个成员回调函数addSpecialChar
            'PDOMySQL', 'addSpecialChar'
        ]);
        $fieldsStr = join(',', $keys); // 在数组最后加上','
        $values = "'" . join("','", $values) . "'"; // 把数组的成员连','连接成字符串
        $sql = "INSERT {$table}({$fieldsStr}) VALUES ({$values})";
        return self::execute($sql);
    }

    /**
     * 添加一条数据有则替换数据内容
     * @param array $data
     * @param string $table
     * @return boolean|number
     */
    public static function replace($data, $table)
    {
        $keys = array_keys($data); // array_keys获得数组的键
        $values = array_values($data); // array_values获得数组的值
        array_walk($keys, [ // array_walk对数组每一个成员回调函数addSpecialChar
            'PDOMySQL', 'addSpecialChar'
        ]);
        $fieldsStr = join(',', $keys); // 在数组最后加上','
        $values = "'" . join("','", $values) . "'"; // 把数组的成员连','连接成字符串
        $sql = "REPLACE INTO {$table}({$fieldsStr}) VALUES ({$values})";
        return self::query($sql);
    }

    // UPDATE TABLE

    /**
     * 更新记录
     * @param array $data
     * @param string $table
     * @param string $where
     * @param string $order
     * @param number $limit
     * @return boolean|number
     */
    public static function update($data, $table, $where = null, $order = null, $limit = 0)
    {
        $sets = '';
        // 把$data的键赋给$key,把$data的值赋给$val
        foreach ($data as $key => $val) {
            $sets .= $key . "='" . $val . "',";
        }
        // echo $sets;
        // 去掉最右边','
        $sets = rtrim($sets, ',');
        $sql = "UPDATE {$table} SET {$sets} " . self::parseWhere($where) . self::parseOrder($order) . self::parseLimit($limit);
        return self::execute($sql);
    }

    /**
     * 删除数据
     * @param string $table
     * @param string $where
     * @param string $order
     * @param number $limit
     * @return boolean|number
     */
    public static function delete($table, $where = null, $order = null, $limit = 0)
    {
        $sql = "DELETE FROM {$table} " . self::parseWhere($where) . self::parseOrder($order) . self::parseLimit($limit);
        return self::execute($sql);
    }

    /**
     * 获得数据总数
     * @param string $fields
     * @param string $table
     * @param string $where
     * @param string $group
     * @param string $having
     * @param string $order
     * @param number $limit
     * @return boolean|number
     */
    public static function total($fields = '*', $table, $where = null, $group = null, $having = null, $order = null, $limit = null)
    {
        $sql = 'SELECT count(' . self::parseFields($fields) . ') FROM ' . $table . self::parseWhere($where) . self::parseGroup($group) . self::parseHaving($having) . self::parseOrder($order) . self::parseLimit($limit);
        return self::getAll($sql);
    }

    /**
     * 查询数据
     * @param string $sql
     * @return boolean
     */
    public static function query($sql = '')
    {
        $link = self::$link;
        if (!$link) {
            return false;
        }
        // 判断$PDOStatement是否有结果集,有释放结果集
        if (!empty(self::$PDOStatement)) {
            self::free();
        }
        self::$queryStr = $sql;
        // 把$sql赋值给$queryStr 保存sql语句
        self::$PDOStatement = $link->prepare(self::$queryStr);
        $result = self::$PDOStatement->execute();
        self::haveErrorThrowException();
        return $result;
    }

    /**
     * 得到最后执行SQL语句
     * @return boolean|string
     */
    public static function getLastSQL()
    {
        $link = self::$link;
        if (!$link) {
            return false;
        }
        return self::$queryStr;
    }

    /**
     * 得到上一步插入操作产生AUTO_INCREMENT
     * @return boolean|string
     */
    public static function getLastInsertId()
    {
        $link = self::$link;
        if (!$link) {
            return false;
        }
        return self::$lastInsertId;
    }

    /**
     * 得到数据库版本号
     * @return boolean|mixed
     */
    public static function getDBVersion()
    {
        $link = self::$link;
        if (!$link) {
            return false;
        }
        return self::$dbVersion;
    }

    /**
     * 得到上一次的数据的错误信息
     * @return boolean|string
     */
    public static function getError()
    {
        $link = self::$link;
        if (!$link) {
            return false;
        }
        return self::$error;
    }

    /**
     * 得到数据库的数据表
     * @return string|mixed[]
     */
    public static function showTables()
    {
        $tables = [];
        $tablesStr = 'Empty set';
        if (self::query('SHOW TABLES')) {
            $result = self::getAll();
            // 遍历$result 每次循环把当前单元的键赋值给$key,把值赋值给$val
            foreach ($result as $key => $val) {
                // current 返回数组中的当前单元
                $tables[$key] = current($val);
            }
        }
        return count($tables > 0) ? $tables : $tablesStr;
    }

    /**
     * 解析字段
     * @param string $fields
     * @return string|unknown
     */
    public static function parseFields($fields)
    {
        $fieldsStr = '*';
        if (is_array($fields)) {
            array_walk($fields, [
                'PDOMySQL',
                'addSpecialChar'
            ]);
            $fieldsStr = implode(',', $fields);
        } elseif (is_string($fields) && !empty($fields)) {
            if (strpos($fields, '`') === false) {
                $fields = explode(',', $fields);
                array_walk($fields, [
                    'PDOMySQL',
                    'addSpecialChar'
                ]);
                $fieldsStr = implode(',', $fields);
            } else {
                $fieldsStr = $fields;
            }
        } else {
            $fields = '*';
        }
        return $fieldsStr;
    }

    /**
     * 解析where
     * @param string $where
     * @return string
     */
    public static function parseWhere($where)
    {
        $whereStr = '';
        if (is_string($where) && !empty($where)) {
            $whereStr = ' WHERE ' . $where;
        }
        return $whereStr;
    }

    /**
     * 解析group
     * @param string $group
     * @return string
     */
    public static function parseGroup($group)
    {
        $groupStr = '';
        if (is_array($group)) {
            $groupStr .= ' GROUP BY ' . implode(',', $group);
        } elseif (is_string($group) && !empty($group)) {
            $groupStr .= ' GROUP BY ' . $group;
        }
        return $groupStr;
    }

    /**
     * 解析having
     * @param string $having
     * @return string
     */
    public static function parseHaving($having)
    {
        $havingStr = '';
        if (is_string($having) && !empty($having)) {
            $havingStr .= ' HAVING ' . $having;
        }
        return $havingStr;
    }

    /**
     * 解析order
     * @param string $order
     * @return string
     */
    public static function parseOrder($order)
    {
        $orderStr = '';
        if (is_array($order)) {
            $orderStr .= ' GROUP BY ' . join(',', $order);
        } elseif (is_string($order) && !empty($order)) {
            $orderStr .= ' GROUP BY ' . $order;
        }
        return $orderStr;
    }

    /**
     * 解析limit
     * @param unknown $limit
     * @return string
     */
    public static function parseLimit($limit)
    {
        $limitStr = '';
        if (is_array($limit)) {
            if (count($limit) > 1) {
                $limitStr .= ' LIMIT ' . $limit[0] . ',' . $limit[1];
            } else {
                $limitStr .= ' LIMIT ' . $limit[0];
            }
        } elseif (is_string($limit) && !empty($limit)) {
            $limitStr .= ' LIMIT ' . $limit;
        }
        return $limitStr;
    }

    /**
     *
     * @param string $sql
     * @return boolean|number
     */
    public static function execute($sql = null)
    {
        $link = self::$link;
        if (!$link) {
            return false;
        }
        self::$queryStr = $sql;
        if (!empty(self::$PDOStatement)) {
            self::free();
        }
        $result = $link->exec(self::$queryStr);
        self::haveErrorThrowException();
        if ($result) {
            self::$lastInsertId = $link->lastInsertId();
            self::$numRows = $result;
            return self::$numRows;
        } else {
            return false;
        }
    }

    /**
     * 释放结果集
     */
    public static function free()
    {
        self::$PDOStatement = null;
    }

    /**
     * 用过'`'引用字段
     * @param unknown $value
     */
    public static function addSpecialChar(&$value)
    {
        if ($value === '*' || strpos($value, '.') !== false || strpos($value, '`') !== false) {
            // 不作操作
        } elseif (strpos($value, '`') === false) {
            $value = '`' . trim($value) . '`';
        }
        return $value;
    }

    /**
     * 自定义错误处理
     * @param unknown $errmsg
     */
    public static function throw_exception($errmsg)
    {
        $printfInfo = <<<EOF
		<link rel="stylesheet" href="http://cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <div class="container">
    		<div class="row">
    			<div class="col-md-8">
    				<div class="panel">
    					<div class="alert-danger text-center">$errmsg</div>
    				</div>
    			</div>
    		</div>
    	</div>
EOF;
        echo $printfInfo;
    }

    /**
     * 打印出query失败时候的错误信息
     * @return boolean
     */
    public static function haveErrorThrowException()
    {
        $obj = empty(self::$PDOStatement) ? self::$link : self::$PDOStatement;
        $errinfo = $obj->errorInfo();
        if ($errinfo[0] != '00000') {
            self::$error = 'SQLSTATE:&nbsp' . $errinfo[0] . ' SQL Error ' . $errinfo[2] . '<br/>Error SQL ' . self::$queryStr;
            self::throw_exception(self::$error);
            return false;
        }
        if (self::$queryStr == '') {
            return false;
        }
    }

    /**
     * 销毁连接对象,关闭数据库
     */
    public static function close()
    {
        self::$link = null;
    }
}
