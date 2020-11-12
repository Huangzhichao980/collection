<?php
namespace panthsoni\collection\common;

class CommonTools
{
    public function __construct(){

    }

    /**
     * 数据验证
     * @param $data
     * @param $validate
     * @param $scene
     * @return array
     * @throws \Exception
     */
    public static function validate($data,$validate,$scene){
        /*数据接收*/
        if (!is_array($data)) throw new \Exception('参数必须为数组',-10001);

        /*验证场景验证*/
        if (!$scene) throw new \Exception('场景不能为空',-10002);

        $validate->scene($scene);

        /*数据验证*/
        if (!$validate->check($data)){
            throw new \Exception($validate->getError(),-10003);
        }

        $scene = $validate->scene[$scene];
        $_scene = [];
        foreach ($scene as $key => $val){
            if(is_numeric($key)){
                $_scene[] = $val;
            }else{
                $_scene[] = $key;
            }
        }

        $_data = [];
        foreach ($data as $key=>$val){
            if ($val === '' || $val === null) continue;

            if(is_numeric($key)){
                if(in_array($key,$_scene)) $_data[$key] = $val;
            }else{
                if(in_array($key,$_scene)) $_data[$key] = $val;
            }
        }

        return $_data;
    }
}