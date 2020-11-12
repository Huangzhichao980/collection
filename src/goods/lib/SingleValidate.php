<?php
namespace panthsoni\collection\goods\lib;

use panthsoni\collection\common\CommonValidate;

class SingleValidate extends CommonValidate
{
    protected $rule = [
        'link|链接地址' => 'url'
    ];

    public $scene = [
        'getResult' => ['link'=>'require|url'],
    ];
}