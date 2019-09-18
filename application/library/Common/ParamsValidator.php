<?php
/**
 * Created by PhpStorm.
 * User: weijin
 * Date: 17/7/12
 * Time: 18:54
 */
namespace App\Library\Common;

use Illuminate\Validation\Validator;
use Symfony\Component\Translation\Translator;


class ParamsValidator extends Validator
{
    public $data;
    public $rules;

    public static $message = [
        'active_url' => ':attribute URL不能正确解析',
        'url' => ':attribute URL格式不正确',
        'array' => ':attribute 不是一个有效的数组',
        'before' => ':attribute 不在指定日期:date之前', // before:2017-05-04
        'after' => ':attribute 不在指定日期:date之后', // after:2017-05-04
        'date' => ':attribute 不是一个有效的日期',
        'image' => ':attribute 不是一个图片文件',
        'upload_mime' => ':attribute 文件MIME类型不在给定清单中: :values', // mimes:jpeg,bmp,png
        'required' => ':attribute 为必填项',
        'required_if' => ':attribute 当字段:other的值等于:value时必填 ',
        'required_with_all' => ':attribute 当所有指定字段:values都有值时必填',
        'required_without_all' => ':attribute 当所有指定字段:values没有值时必填',
        'integer' => ':attribute 必须为整数',
        'numeric' => ':attribute 必须为数字',
        'email' => ':attribute 邮箱格式不对',
        'json' => ':attribute 必须为json格式',
        'between' => ':attribute 取值区间为:min - :max',
        'min' => ':attribute  最小值为:min',
        'max' => ':attribute  最大值为:max',
        'in' => ':attribute  必须为给定清单:values中的值',
        'not_in' => ':attribute  不能为给定清单:values中的值',
        'is_list' => 'attribute 必须为[xxx,xx]的',
        'json_list' => ':attribute 必须为[{xxx},{xxx}]的json字符串',
        'json_object' => ':attribute 必须为{xxx}的json字符串',
        'order_by' => ':attribute 格式必须为-xxx,xx 且只能给为定清单:values中的值',
        'regex' => ':attribute 格式错误',
    ];

    public function __construct($customMessage = [])
    {
        $translator = new Translator('zh_CN');

        parent::__construct($translator, [], [], array_merge(self::$message, $customMessage));
    }

    public function runValid($rules, array $params = [])
    {
        $res = [];
        if (!empty($rules)) {
            $this->data = $this->parseData($params);
            $this->rules = $this->explodeRules($this->customRule($rules));
            $this->setAttributeNames($this->customAttr($rules));
            if ($this->messages()) {
                $res = $this->messages()->toArray();
            }
        }
        return $res;
    }

    public function getValidData()
    {
        $fields_array = array_fill_keys(array_keys($this->rules), '');
        $valid_data = array_intersect_key($this->data, $fields_array);
        return $valid_data;
    }

    //去掉attr的描述
    //例如:name|名称 => 'required' 返回 name => 'required'
    private function customRule($rules)
    {
        $customRule = [];
        foreach ($rules as $attr => $val) {
            list($attr) = explode('|', $attr);
            $customRule[$attr] = $val;
        }
        return $customRule;
    }

    //获取attr的描述
    //例如:name|名称 => 'required' 返回 name => '名称'
    private function customAttr($rules)
    {
        $customAttr = [];
        foreach ($rules as $attr => $val) {
            list($key, $desc) = explode('|', $attr . '|');
            !$desc && $desc = $key;
            $customAttr[$key] = $desc;
        }
        return $customAttr;
    }

    // todo 实现回调功能
    protected function validateCallback($attribute, $value, $parameters)
    {
    }

    // 实现sometime功能, 有则验证没有不验证
    protected function validateContinue($attribute)
    {
        if (isset($this->data[$attribute]) && strlen($this->data[$attribute])) {
            return true;
        }
    }

    // 判断是否是list类型, [xxx,xxx]
    protected function validateIsList($attribute, $value)
    {
        if (!$this->hasAttribute($attribute)) {
            return true;
        }
        return isList($value);
    }

    protected function validateJsonList($attribute, $value)
    {
        if (!$this->hasAttribute($attribute)) {
            return true;
        }
        //判断是否是json
        if (!$this->validateJson($attribute, $value)) {
            return false;
        }
        $value = json_decode($value, true);

        return $this->validateIsList($attribute, $value);
    }

    protected function validateJsonObject($attribute, $value)
    {
        if (!$this->hasAttribute($attribute)) {
            return true;
        }
        //判断是否是json
        if (!$this->validateJson($attribute, $value)) {
            return false;
        }
        return isDict(json_decode($value, true)) ? true : false;
    }

    protected function validateJsonDecode($attribute, $value)
    {
        if ($this->hasAttribute($attribute)) {
            $this->validateJson($attribute, $value) && ($this->data[$attribute] = json_decode($value, true));
        }

        return true;
    }

    protected function validateJsonEncode($attribute, $value)
    {
        if ($this->hasAttribute($attribute)) {
            is_array($value) && ($this->data[$attribute] = json_encode($value, true));
        }
        return true;
    }

    protected function validateOrderBy($attribute, $value, $parameters)
    {
        if (!preg_match('/^((-)?[^,]+,)*((-)?[^,]+)?$/', $value)) {
            return false;
        }
        $order_by = explode(',', $value);
        $order_by = array_map(function ($value) {
            return ltrim($value, '-');
        }, $order_by);

        return array_diff($order_by, $parameters) ? false : true;
    }

    protected function validateUploadMime($attribute, $value, $parameters)
    {
        if (!empty($value["name"])) {
            $file_type = pathinfo($value['name'], PATHINFO_EXTENSION);
            return in_array($file_type, $parameters);
        }
        return false;
    }

    protected function replaceOrderBy($message, $attribute, $rule, $parameters)
    {
        return str_replace(':values', implode(', ', $parameters), $message);
    }

    protected function replaceRegex($message, $attribute, $rule, $parameters)
    {
        return str_replace(':regex', $parameters[0], $message);
    }

    protected function replaceUploadMime($message, $attribute, $rule, $parameters)
    {
        return str_replace(':values', implode(', ', $parameters), $message);
    }
}

