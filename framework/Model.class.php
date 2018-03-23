<?php
class Model
{
    protected $_PDO = null;

    protected function _init()
    {
        // 		date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
        $this->_PDO = new PDOMySQL();
    }

    public function __construct()
    {
        $this->_init();
    }
}
