<?php

class Factory
{
    /**
     * 生成模型的单例对象
     * @param string $model_name
     * @return object
     */
    public static function setModel($model_name)
    {
        static $model_list = []; // 存储已经实例化好的模型对象，下标模型名，值模型对象
        // 判断当前模型是否已经实例化
        if (!isset($model_list[$model_name])) {
            $model_list[$model_name] = new $model_name(); // 可变标识符，可变类
        }
        return $model_list[$model_name];
    }
}
