<?php
namespace panthsoni\collection\goods;

use panthsoni\collection\goods\lib\GoodsClient;

class Goods
{
    protected static $options = [];
    public function __construct($options=[]){
        self::$options = array_merge(self::$options,$options);
    }

    /**
     * 初始化
     * @param array $options
     * @return GoodsClient
     */
    public static function init($options=[]){
        self::$options = array_merge(self::$options,$options);
        return new GoodsClient(self::$options);
    }
}