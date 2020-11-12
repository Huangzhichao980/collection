<?php
namespace panthsoni\collection\goods\lib;

use panthsoni\collection\common\CommonTools;

class Tools extends CommonTools
{
    /**
     * Tools constructor.
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * 检查淘宝，天猫，1688的商品链接
     * @param $linkArr
     * @param $link
     * @return string
     * @throws \Exception
     */
    public static function checkUrl($linkArr,$link){
        /*检测链接地址是否有?*/
        if (!count($linkArr)) throw new \Exception('链接地址有误',-20001);

        /*检测链接地址信息2*/
        if (!isset($linkArr[1])) {
            if (strpos($link, '1688') !== false && strpos($link, 'offer') !== false) return trim($linkArr[0]);
            if (strpos($link, 'item.jd') !== false) return trim($linkArr[0]);
            throw new \Exception('链接地址有误',-20001);
        }

        /*检测链接地址信息3*/
        $arrLineValue = explode('&', $linkArr[1]);
        if (strpos($link, '1688') !== false && strpos($link, 'offer') !== false) return trim($linkArr[0]);
        if (strpos($link, 'item.jd') !== false) return trim($linkArr[0]);
        if (!is_array($arrLineValue)) throw new \Exception('链接地址有误',-20001);
        if (!strpos(trim($linkArr[0]), 'item.htm')) throw new \Exception('链接地址有误',-20001);

        /*链接参数*/
        $lastStr = '';
        foreach ($arrLineValue as $k => $v) {
            if (substr(strtolower($v), 0, 3) == 'id=') {
                $lastStr = trim($v);
                break;
            }
        }
        if (!$lastStr) throw new \Exception('链接地址有误',-20001);

        return trim($linkArr[0]) . '?' . $lastStr;
    }

    /**
     * GET 请求
     * @param string $url
     * @param $webName
     * @param $webCookie
     * @param int $timeOut
     * @return bool|string
     */
    public static function curlGet($url='',$webName,$webCookie,$timeOut=25){
        $ch = curl_init();
        /*跳过证书检查*/
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);

        /*从证书中检查SSL加密算法是否存在*/
        if (stripos($url,"https://") !== FALSE) curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);

        $headers = ['user-agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36'];
        if($webName) $headers[] = $webCookie;

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt ($ch,CURLOPT_REFERER,"www.crmeb.com");
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($ch,CURLOPT_TIMEOUT,$timeOut);
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) return false;
        curl_close($ch);

        return mb_convert_encoding($response,'utf-8','GB2312');
    }

    /**
     * 设置字符串字符集
     * @param $str
     * @return string
     */
    public static function Utf8String($str){
        $encode = mb_detect_encoding($str, array("ASCII", 'UTF-8', "GB2312", "GBK", 'BIG5'));
        if (strtoupper($encode) != 'UTF-8') $str = mb_convert_encoding($str, 'utf-8', $encode);

        return $str;
    }

    /**
     * 获取淘宝商品组图
     * @param string $html
     * @return array|string
     */
    protected static function getTaobaoImg($html = ''){
        /*正则匹配图片内容*/
        preg_match('/auctionImages([^<>]*)"]/', $html, $imgarr);
        if (!isset($imgarr[1])) return false;
        $arr = explode(',', $imgarr[1]);
        foreach ($arr as $k => &$v) {
            $str = str_replace(['"', ' ', '', ':['], '', trim($v));
            if (strpos($str, '?')) $str = trim(explode('?', $str)[0]);

            $_i_url = strpos($str, 'http') ? $str : 'http:' . $str;
            if (!self::imgExists($_i_url)){
                unset($arr[$k]);
                continue;
            }

            $v = $_i_url;
        }

        return array_unique($arr);
    }

    /**
     * 获取淘宝商品描述
     * @param string $html
     * @return bool|string
     */
    protected static function getTaobaoDesc($html = ''){
        preg_match("/dscnew.taobao.com.+?'/i", $html, $descarr);
        $url = substr($descarr[0],0,-1);
        if (!$url) return false;

        return 'https://'.$url;
    }

    /**
     * 判断图片是否存在
     * @param string $url
     * @return bool
     */
    protected static function imgExists($url = ''){
        ini_set("max_execution_time", 0);
        $str = @file_get_contents($url, 0, null, 0, 1);
        if (strlen($str) <= 0 || !$str) return false;

        return true;
    }

    /**
     * 提取商品描述中的所有图片
     * @param string $desc
     * @return array|string
     */
    protected static function decodeDesc($desc = ''){
        if (!$desc) return false;
        $desc = trim($desc);
        preg_match_all('/<img[^>]*?src="([^"]*?)"[^>]*?>/i', $desc, $match);
        if (!isset($match[1]) || count($match[1]) <= 0) {
            preg_match_all('/:url(([^"]*?));/i', $desc, $match);
            if (!isset($match[1]) || count($match[1]) <= 0) return $desc;
        } else {
            preg_match_all('/:url(([^"]*?));/i', $desc, $newmatch);
            if (isset($newmatch[1]) && count($newmatch[1]) > 0) $match[1] = array_merge($match[1], $newmatch[1]);
        }
        $match[1] = array_unique($match[1]); //去掉重复
        foreach ($match[1] as $k => &$v) {
            $_tmp_img = str_replace([')', '(', ';'], '', $v);
            $_tmp_img = strpos($_tmp_img, 'http') ? $_tmp_img : 'http:' . $_tmp_img;
            if (strpos($v, '?')) {
                $_tmp_img = trim(explode('?', $v)[0]);
            }
            $_urls = str_replace(['\'', '"'], '', $_tmp_img);
            if (self::imgExists($_urls)) $v = $_urls;
        }

        return $match[1];
    }

    /**
     * 获取京东商品组图
     * @param string $html
     * @return array|bool
     */
    protected static function getJdImg($html = ''){
        //获取图片服务器网址
        preg_match('/<img(.*?)id="spec-img"(.*?)data-origin=\"(.*?)\"[^>]*>/', $html, $img);
        if (!isset($img[3])) return false;
        $info = parse_url(trim($img[3]));
        if (!$info['host']) return false;
        if (!$info['path']) return false;
        $_tmparr = explode('/', trim($info['path']));
        $url = 'http://' . $info['host'] . '/' . $_tmparr[1] . '/' . str_replace(['jfs', ' '], '', trim($_tmparr[2]));
        preg_match('/imageList:(.*?)"],/is', $html, $img);
        if (!isset($img[1])) return false;

        $_arr = explode(',', $img[1]);
        foreach ($_arr as $k => &$v) {
            $_str = $url . str_replace(['"', '[', ']', ' '], '', trim($v));
            if (strpos($_str, '?')) {
                $_tarr = explode('?', $_str);
                $_str = trim($_tarr[0]);
            }
            if (self::imgExists($_str)) {
                $v = $_str;
            } else {
                unset($_arr[$k]);
            }
        }

        return array_unique($_arr);
    }

    /**
     * 获取京东商品描述
     * @param string $html
     * @return string
     */
    protected static function getJdDesc($html = ''){
        preg_match('/,(.*?)desc:([^<>]*)\',/i', $html, $descarr);
        if (!isset($descarr[1]) && !isset($descarr[2])) return '';
        $tmpArr = explode(',', $descarr[2]);
        if (count($tmpArr) > 0) {
            $descarr[2] = trim($tmpArr[0]);
        }
        $replace_arr = ['\'', '\',', ' ', ',', '/*', '*/'];
        if (isset($descarr[2])) {
            $d_url = str_replace($replace_arr, '', $descarr[2]);
            return self::formatDescUrl(strpos($d_url, 'http') ? $d_url : 'http:' . $d_url);
        }
        $d_url = str_replace($replace_arr, '', $descarr[1]);
        $d_url = self::formatDescUrl($d_url);
        $d_url = rtrim(rtrim($d_url, "?"), "&");

        return substr($d_url, 0, 4) == 'http' ? $d_url : 'http:' . $d_url;
    }

    /**
     * 处理下京东商品描述网址
     * @param string $url
     * @return string
     */
    protected static function formatDescUrl($url = ''){
        if (!$url) return '';
        $url = substr($url, 0, 4) == 'http' ? $url : 'http:' . $url;
        if (!strpos($url, '&')) {
            $_arr = explode('?', $url);
            if (!is_array($_arr) || count($_arr) <= 0) return $url;
            return trim($_arr[0]);
        } else {
            $_arr = explode('&', $url);
        }
        if (!is_array($_arr) || count($_arr) <= 0) return $url;
        unset($_arr[count($_arr) - 1]);
        $new_url = '';
        foreach ($_arr as $k => $v) {
            $new_url .= $v . '&';
        }
        return !$new_url ? $url : $new_url;
    }

    /**
     * 获取1688商品组图
     * @param string $html
     * @return array|string
     */
    protected static function get1688Img($html = ''){
        preg_match('/<ul class=\"nav nav-tabs fd-clr\">(.*?)<\/ul>/is', $html, $img);
        if (!isset($img[0])) return '';

        preg_match_all('/preview":"(.*?)\"\}\'>/is', $img[0], $arrb);
        if (!isset($arrb[1]) || count($arrb[1]) <= 0) return '';

        $thumb = [];
        $gaoqing = [];
        foreach ($arrb[1] as $k => $v) {
            $_str = str_replace(['","original":"'], '*', $v);
            $_arr = explode('*', $_str);
            if (is_array($_arr) && isset($_arr[0]) && isset($_arr[1])) {
                if (strpos($_arr[0], '?')) {
                    $_tarr = explode('?', $_arr[0]);
                    $_arr[0] = trim($_tarr[0]);
                }
                if (strpos($_arr[1], '?')) {
                    $_tarr = explode('?', $_arr[1]);
                    $_arr[1] = trim($_tarr[0]);
                }
                if (self::imgExists($_arr[0])) $thumb[] = trim($_arr[0]);
                if (self::imgExists($_arr[1])) $gaoqing[] = trim($_arr[1]);
            }
        }

        $res = ['thumb' => array_unique($thumb), 'gaoqing' => array_unique($gaoqing)];  //缩略图片和高清图片
        return $res;
    }

    /**
     * 获取1688商品描述
     * @param string $html
     * @return mixed|string
     */
    protected static function get1688Desc($html = ''){
        preg_match('/data-tfs-url="([^<>]*)data-enable="true"/', $html, $descarr);
        if (!isset($descarr[1])) return '';
        return str_replace(['"', ' '], '', $descarr[1]);
    }

    /**
     * 获取天猫商品组图
     * @param string $html
     * @return array|string
     */
    protected static function getTianMaoImg($html = ''){
        $pic_size = '430';
        preg_match('/<img[^>]*id="J_ImgBooth"[^r]*rc=\"([^"]*)\"[^>]*>/', $html, $img);
        if (isset($img[1])) {
            $_arr = explode('x', $img[1]);
            $filename = $_arr[count($_arr) - 1];
            $pic_size = intval(substr($filename, 0, 3));
        }
        preg_match('|<ul id="J_UlThumb" class="tb-thumb tm-clear">(.*)</ul>|isU', $html, $match);
        preg_match_all('/<img src="(.*?)" \//', $match[1], $images);
        if (!isset($images[1])) return '';
        foreach ($images[1] as $k => &$v) {
            $tmp_v = trim($v);
            $_arr = explode('x', $tmp_v);
            $_fname = $_arr[count($_arr) - 1];
            $_size = intval(substr($_fname, 0, 3));
            if (strpos($tmp_v, '://')) {
                $_arr = explode(':', $tmp_v);
                $r_url = trim($_arr[1]);
            } else {
                $r_url = $tmp_v;
            }
            $str = str_replace($_size, $pic_size, $r_url);
            if (strpos($str, '?')) {
                $_tarr = explode('?', $str);
                $str = trim($_tarr[0]);
            }
            $_i_url = strpos($str, 'http') ? $str : 'http:' . $str;
            if (self::imgExists($_i_url)) {
                $v = $_i_url;
            } else {
                unset($images[1][$k]);
            }
        }

        return array_unique($images[1]);
    }

    /**
     * 获取天猫商品描述
     * @param string $html
     * @return string
     */
    protected static function getTianMaoDesc($html = ''){
        preg_match('/descUrl":"([^<>]*)","httpsDescUrl":"/', $html, $descarr);
        if (!isset($descarr[1])) {
            preg_match('/httpsDescUrl":"([^<>]*)","fetchDcUrl/', $html, $descarr);
            if (!isset($descarr[1])) return '';
        }
        return strpos($descarr[1], 'http') ? $descarr[1] : 'http:' . $descarr[1];
    }

    /**
     * 获取请求信息
     * @param $url
     * @param array $data
     * @param bool $header
     * @param int $timeout
     * @return bool|string
     */
    protected static function getRequest($url,$data=array(),$header=false,$timeout=10){
        if (!empty($data)) {
            $url .= (stripos($url,'?') === false ?'?':'&');
            $url .= (is_array($data)?http_build_query($data):$data);
        }

        return self::request($url,'get',array(),$header,$timeout);
    }

    /**
     * curl请求
     * @param $url
     * @param string $method
     * @param array $data
     * @param bool $header
     * @param int $timeout
     * @return bool|string
     */
    protected static function request($url, $method = 'get', $data = array(), $header = false, $timeout = 15){
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if (strtoupper($method) == 'POST') curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        if ($header !== false) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        //https请求
        if (1 == strpos("$" . $url, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_error($curl);

        list($content, $status) = [curl_exec($curl),curl_getinfo($curl),curl_close($curl)];
        $content = trim(substr($content, $status['header_size']));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * 淘宝设置产品
     * @param $html
     * @param $webName
     * @param $webCookie
     * @param $timeout
     * @return array
     */
    public static function setProductInfoTaobao($html,$webName,$webCookie,$timeout=60){
        $productInfo = [];

        /*获取轮播图*/
        $images = self::getTaobaoImg($html);
        $images = array_merge($images);
        $productInfo['slider_image'] = isset($images['gaoqing'])?$images['gaoqing']:(array)$images;
        $productInfo['slider_image'] = array_slice($productInfo['slider_image'],0,5);

        /*获取产品详情请求链接*/
        $link = self::getTaobaoDesc($html);

        /*获取请求内容*/
        $desc_json = self::curlGet($link,$webName,$webCookie,$timeout);

        /*转换字符集*/
        $desc_json = self::Utf8String($desc_json);

        /*截取掉多余字符*/
        $productInfo['test'] = $desc_json;
        $desc_json = str_replace('var desc=\'', '', $desc_json);
        $desc_json = str_replace(["\n", "\t", "\r"], '', $desc_json);
        $content = substr($desc_json, 0, -2);
        $productInfo['description'] = $content;

        /*获取详情图*/
        $description_images = self::decodeDesc($productInfo['description']);
        $productInfo['description_images'] = is_array($description_images)?$description_images:[];
        $productInfo['image'] = is_array($productInfo['slider_image']) && isset($productInfo['slider_image'][0])?$productInfo['slider_image'][0]:'';

        return $productInfo;
    }

    /**
     * 天猫设置产品
     * @param $html
     * @param $webName
     * @param $webCookie
     * @param $timeout
     * @return array
     */
    public static function setProductInfoTmall($html,$webName,$webCookie,$timeout=60){
        $productInfo = [];

        /*获取轮播图*/
        $images = self::getTianMaoImg($html);
        $images = array_merge($images);
        $productInfo['slider_image'] = $images;
        $productInfo['slider_image'] = array_slice($productInfo['slider_image'],0,5);
        $productInfo['image'] = is_array($productInfo['slider_image']) && isset($productInfo['slider_image'][0])?$productInfo['slider_image'][0]:'';

        /*获取产品详情请求链接*/
        $link = self::getTianMaoDesc($html);

        /*获取请求内容*/
        $desc_json = self::curlGet($link,$webName,$webCookie,$timeout);

        /*转换字符集*/
        $desc_json = self::Utf8String($desc_json);

        /*截取掉多余字符*/
        $desc_json = str_replace('var desc=\'', '', $desc_json);
        $desc_json = str_replace(["\n", "\t", "\r"], '', $desc_json);
        $content = substr($desc_json, 0, -2);
        $productInfo['description'] = $content;

        /*获取详情图*/
        $description_images = self::decodedesc($productInfo['description']);
        $productInfo['description_images'] = is_array($description_images)?$description_images:[];

        return $productInfo;
    }

    /**
     * 1688设置产品
     * @param $html
     * @param $webName
     * @param $webCookie
     * @param $timeout
     * @return array
     */
    public static function setProductInfo1688($html,$webName,$webCookie,$timeout=60){
        $productInfo = [];

        /*获取轮播图*/
        $images = self::get1688Img($html);
        if (isset($images['gaoqing'])) {
            $images['gaoqing'] = array_merge($images['gaoqing']);
            $productInfo['slider_image'] = $images['gaoqing'];
        } else{
            $productInfo['slider_image'] = $images;
        }
        $productInfo['slider_image'] = array_slice($productInfo['slider_image'],0,5);
        $productInfo['image'] = is_array($productInfo['slider_image']) && isset($productInfo['slider_image'][0])?$productInfo['slider_image'][0]:'';

        /*获取产品详情请求链接*/
        $link = self::get1688Desc($html);

        /*获取请求内容*/
        $desc_json = self::curlGet($link,$webName,$webCookie,$timeout);

        /*转换字符集*/
        $desc_json = self::Utf8String($desc_json);
        $productInfo['test'] = $desc_json;

        /*截取掉多余字符*/
        $desc_json = str_replace('var offer_details=', '', $desc_json);
        $desc_json = str_replace(["\n", "\t", "\r"], '', $desc_json);
        $desc_json = substr($desc_json, 0, -1);
        $descArray = json_decode($desc_json, true);
        if (!isset($descArray['content'])) $descArray['content'] = '';
        $productInfo['description'] = $descArray['content'];

        /*获取详情图*/
        $description_images = self::decodedesc($productInfo['description']);
        $productInfo['description_images'] = is_array($description_images)?$description_images:[];

        return $productInfo;
    }

    /**
     * JD设置产品
     * @param $html
     * @return array
     */
    public static function setProductInfoJd($html,$webName,$webCookie,$timeOut=60){
        $productInfo = [];

        /*获取产品详情请求链接*/
        $desc_url = self::getJdDesc($html);

        /*获取请求内容*/
        $desc_json = self::getRequest($desc_url);

        /*转换字符集*/
        $desc_json = self::Utf8String($desc_json);

        //截取掉多余字符
        if (substr($desc_json, 0, 8) == 'showdesc') $desc_json = str_replace('showdesc','',$desc_json);
        $desc_json = str_replace('data-lazyload=', 'src=', $desc_json);
        $descArray = json_decode($desc_json, true);
        if (!$descArray) $descArray = ['content' => ''];

        /*获取轮播图*/
        $images = self::getJdImg($html);
        $images = array_merge($images,[]);
        $productInfo['slider_image'] = $images;
        $productInfo['image'] = is_array($productInfo['slider_image'])?$productInfo['slider_image'][0]:'';
        $productInfo['description'] = $descArray['content'];

        /*获取详情图*/
        $description_images = self::decodedesc($descArray['content']);
        $productInfo['description_images'] = is_array($description_images)?$description_images:[];

        return $productInfo;
    }
}