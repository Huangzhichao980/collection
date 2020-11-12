<?php
namespace panthsoni\collection\goods\lib;

class GoodsClient
{
    /**
     * 配置参数
     * @var array
     */
    protected static $options = [];

    /**
     * 必选参数
     * @var array
     */
    protected static $params = [];

    /**
     * 可选参数
     * @var array
     */
    protected static $bizParams = [];

    /**
     * 请求平台名称 taobao alibaba tmall jd
     * @var string
     */
    protected static $webName = 'taobao';

    /**
     * cookie 采集前请配置自己的 cookie,获取方式浏览器登录平台，F12或查看元素 network->headers 查看Request Headers 复制cookie 到下面变量中
     * @var array
     */
    protected static $webCookie = [
        //淘宝
        'taobao' =>'cookie: miid=8289590761042824660; thw=cn; cna=bpdDExs9KGgCAXuLszWnEXxS; hng=CN%7Czh-CN%7CCNY%7C156; tracknick=taobaorongyao; _cc_=WqG3DMC9EA%3D%3D; tg=0; enc=WQPStocTopRI3wEBOPpj8VUDkqSw4Ph81ASG9053SgG8xBMzaOuq6yMe8KD4xPBlNfQST7%2Ffsk9M9GDtGmn6iQ%3D%3D; t=4bab065740d964a05ad111f5057078d4; cookie2=1965ea371faf24b163093f31af4120c2; _tb_token_=5d3380e119d6e; v=0; mt=ci%3D-1_1; _m_h5_tk=61bf01c61d46a64c98209a7e50e9e1df_1572349453522; _m_h5_tk_enc=9d9adfcbd7af7e2274c9b331dc9bae9b; l=dBgc_jG4vxuski7DBOCgCuI8aj7TIIRAguPRwN0viOCKUxT9CgCDAJt5v8PWVNKO7t1nNetzvui3udLHRntW6KTK6MK9zd9snxf..; isg=BJWVXJ3FZGyiWUENfGCuywlwpJePOkncAk8hmRc6WoxbbrVg3-Jadf0uODL97mFc',
        //阿里巴巴 1688
        'alibaba' =>'',
        //天猫 可以和淘宝一样
        'tmall' =>'cookie: miid=8289590761042824660; thw=cn; cna=bpdDExs9KGgCAXuLszWnEXxS; hng=CN%7Czh-CN%7CCNY%7C156; tracknick=taobaorongyao; _cc_=WqG3DMC9EA%3D%3D; tg=0; enc=WQPStocTopRI3wEBOPpj8VUDkqSw4Ph81ASG9053SgG8xBMzaOuq6yMe8KD4xPBlNfQST7%2Ffsk9M9GDtGmn6iQ%3D%3D; t=4bab065740d964a05ad111f5057078d4; cookie2=1965ea371faf24b163093f31af4120c2; _tb_token_=5d3380e119d6e; v=0; mt=ci%3D-1_1; _m_h5_tk=61bf01c61d46a64c98209a7e50e9e1df_1572349453522; _m_h5_tk_enc=9d9adfcbd7af7e2274c9b331dc9bae9b; l=dBgc_jG4vxuski7DBOCgCuI8aj7TIIRAguPRwN0viOCKUxT9CgCDAJt5v8PWVNKO7t1nNetzvui3udLHRntW6KTK6MK9zd9snxf..; isg=BJWVXJ3FZGyiWUENfGCuywlwpJePOkncAk8hmRc6WoxbbrVg3-Jadf0uODL97mFc',
        //京东 可不用配置
        'jd' =>''
    ];

    /**
     * 抓取网站主域名
     * @var array
     */
    protected static $grabName = ['taobao','1688','tmall','jd'];

    /**
     * 产品默认字段
     * @var array
     */
    protected static $productInfo = [
        'cate_id' => '',
        'store_name' => '',
        'store_info' => '',
        'unit_name' => '件',
        'price' => 0,
        'keyword' => '',
        'ficti' => 0,
        'ot_price' => 0,
        'give_integral' => 0,
        'postage' => 0,
        'cost' => 0,
        'image' => '',
        'slider_image' => '',
        'add_time' => 0,
        'stock' => 0,
        'description' => '',
        'soure_link' => ''
    ];

    /**
     * GoodsCollectionClient constructor.
     * @param array $params
     */
    public function __construct($params=[]){
        self::$params = $params;
    }

    /**
     * 设置配置信息
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options){
        self::$options = array_merge(self::$options,$options);
        return $this;
    }

    /**
     * 设置必选参数
     * @param array $params
     * @return $this
     */
    public function setParams(array $params){
        self::$params = array_merge(self::$params,$params);
        return $this;
    }

    /**
     * 设置可选参数
     * @param array $bizParams
     * @return $this
     */
    public function setBizParams(array $bizParams){
        self::$bizParams = array_merge(self::$bizParams,$bizParams);
        return $this;
    }

    /**
     * 设置链接
     * @param $link
     * @return $this
     */
    public function setLink($link){
        self::$params['link'] = $link;
        return $this;
    }

    /**
     * 设置网站名称
     * @param $webName
     * @return $this
     */
    public function setWebName($webName){
        self::$webName = $webName;
        return $this;
    }

    /**
     * 设置网站Cookie
     * @param $webCookie
     * @return $this
     */
    public function setWebCookie($webCookie){
        self::$webCookie = array_merge(self::$webCookie,[self::$webName => $webCookie]);
        return $this;
    }

    /**
     * 获取结果
     * @return array|bool
     * @throws \Exception
     */
    public function getResult(){
        $requestParams = Tools::validate(array_merge(self::$options,self::$params,self::$bizParams),new SingleValidate(),'getResult');

        /*是否设置请求网站*/
        if (!self::$webName) throw new \Exception('网站名称缺失',-20002);
        if (!in_array(self::$webName,self::$grabName)) throw new \Exception('网站名称设置有误',-20003);

        /*检测链接*/
        $url = Tools::checkUrl(explode('?',strtolower($requestParams['link'])),strtolower($requestParams['link']));
        if ($url === false) return false;

        /*抓取HTML*/
        $html = Tools::curlGet($url.'&ra'.rand(222222222,999999999),self::$webName,self::$webCookie[self::$webName],60);
        if (!$html) return false;

        /*设置字符串字符集*/
        $html = Tools::Utf8String($html);
        preg_match('/<title>([^<>]*)<\/title>/',$html,$title);

        /*获取商品标题*/
        self::$productInfo['store_name'] = isset($title['1']) ? str_replace(['-淘宝网', '-tmall.com天猫', ' - 阿里巴巴', ' ', '-', '【图片价格品牌报价】京东', '京东', '【行情报价价格评测】'], '', trim($title['1'])) : '';
        self::$productInfo['store_info'] = self::$productInfo['store_name'];

        try {
            /*获取url信息*/
            $pathInfo = pathinfo($url);
            if (!isset($pathInfo['dirname'])) throw new \Exception('解析URL失败',-20004);

            /*提取域名*/
            $parse_url = parse_url($pathInfo['dirname']);
            if (!isset($parse_url['host'])) throw new \Exception('提取域名失败',-20005);

            /*获取第一次.出现的位置和截取域名中的真实域名不带.com后的*/
            $strLength = strpos($parse_url['host'], '.') + 1;
            $funSuffix = substr($parse_url['host'], $strLength, strrpos($parse_url['host'], '.') - $strLength);
            if (!in_array($funSuffix, self::$grabName)) throw new \Exception('您输入的地址不在复制范围内！',-20006);

            /*设拼接设置产品函数*/
            $funName = "setProductInfo" . ucfirst($funSuffix);
            if(!method_exists(new Tools(),$funName)) throw new \Exception('设置产品函数不存在',-20007);

            /*获取商品信息*/
            self::$productInfo = array_merge(self::$productInfo,Tools::$funName($html,self::$webName,self::$webCookie[self::$webName]));
            if (isset(self::$productInfo['description'])) self::$productInfo['description'] = str_replace('"',"'",self::$productInfo['description']);
            if (!self::$productInfo['slider_image']) throw new \Exception('未能获取到商品信息，请确保商品信息有效！',-20008);
        }catch(\Exception $e) {
            return ['line' => $e->getLine(), 'meass' => $e->getMessage()];
        }

        return self::$productInfo;
    }
}