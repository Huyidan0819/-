<?php
/**
 * Created by PhpStorm.
 * User: Jack
 * Date: 2019/1/16
 * Time: 下午 4:21
demo:
$ret = SMS::getInstance()->send('18682395282','SMS_145594580',['code'=>'123456']);
var_dump($ret);
 *
object(stdClass)[6]
public 'Message' => string 'OK' (length=2)
public 'RequestId' => string '53427AB8-5F62-40FE-A229-373503F2F04C' (length=36)
public 'BizId' => string '835200547643838437^0' (length=20)
public 'Code' => string 'OK' (length=2)
 */

namespace Aliyun;

use think\Config;

class SMS
{
    private static $_instance;

    private $_security = false;
    private $_params = [];
    private $_objSignatureHelper;

    const VERIFY_CODE_TEMPLATE = 'SMS_145594580';
    const VERIFY_CODE_EXPIRE = 60;
    const KEY_PHONE_PREFIX = 'SNS_';

    public function __construct()
    {
        $this->_objSignatureHelper = new SignatureHelper();
    }

    /**
     * 获取对象
     * @return SMS
     */
    public static function getInstance()
    {
        if( self::$_instance == false ){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 短信发送
     * @param $phoneNumbers
     * @param $templateCode
     * @param array $templateParam
     * @return bool|\stdClass
     */
    public function send($phoneNumbers,$templateCode,array $templateParam)
    {
        $this->setParams("PhoneNumbers",$phoneNumbers);
        $this->setParams("SignName",'大牛互动'); //Config::get()
        $this->setParams("TemplateCode",$templateCode);
        if(!empty($templateParam) && is_array($templateParam)) {
            $this->setParams("TemplateParam",json_encode($templateParam, JSON_UNESCAPED_UNICODE));
        }
        $params = array_merge($this->_params, array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ));
        // 此处可能会抛出异常，注意catch
        $content = $this->_objSignatureHelper->request(
            'LTAIqJED7gM67svB',
            'NgdERoc8pVnHZxjfDkQtccix9s8yY7',
            "dysmsapi.aliyuncs.com",
            $params,
            $this->_security
        );
        return $content;
    }

    /**
     * 发送手机验证码
     * @param $phoneNumber
     * @param string $key_pirfix
     * @return bool|\stdClass
     */
    public function sendMobileVerifyCode($phoneNumber)
    {
        $code = str_shuffle(mt_rand(10000,99999));
        \think\Cache::set(self::KEY_PHONE_PREFIX.$phoneNumber,$code,self::VERIFY_CODE_EXPIRE);
        return $this->send($phoneNumber,self::VERIFY_CODE_TEMPLATE,['code'=>$code]);
    }

    /**
     * 处理参数
     * @return array
     */
    protected function handleParams(array $params)
    {
        return array_merge($params, array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ));
    }

    /**
     * fixme 必填：是否启用https
     * @param bool $bool
     */
    public function setSecurity($bool = false)
    {
        $this->_security = $bool;
    }

    /**
     * 设置参数
     * @param $name
     * @param $value
     */
    public function setParams($name,$value)
    {
        $this->_params[$name] = $value;
    }
}