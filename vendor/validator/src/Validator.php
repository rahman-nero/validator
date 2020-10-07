<?php
namespace Validator;

final class Validator
{

    private $_rules = [];
    private $_fields = [];
    private $_languages = [];
    private $_lang;
    private $_errors = [];
    private $messagesFields = [];
    protected $url = ['https://', 'http://', 'ftp://'];

    public function __construct(array $rules = [], $_lang = 'ru') {
        $this->_languages = require __DIR__ . '/languages/language.php';
        $this->rules($rules);
        $this->setLanguage($_lang);
    }

    public function validate(array $data) {
        $this->existsRequireFields($data); // проверяет есть ли там обязательные поля

        foreach ($this->_rules as $fieldName => $ruleArr) {
            if (array_key_exists($fieldName, $data)) {
                $field = $data[$fieldName];

                foreach ($ruleArr as $key => $val) {
                    if ($key == 'validate') {
                        $this->checkTypeFields($val, $fieldName, $field);
                    } else {
                        $this->checkFields($key, $fieldName, $field, $val);
                    }
                }

            }
        }
    }

    protected function checkFields($method, $field, $value, $argMethod){
        switch ($method) {
            case 'minLength':
                $this->minLength($field, $value, $argMethod);
                break;

            case 'maxLength':
                $this->maxLength($field, $value,$argMethod);
                break;

            case 'length':
                $this->fixedLength($field, $value, $argMethod);
                break;

            case 'min':
                $this->min($field, $value,$argMethod);
                break;

            case 'max':
                $this->max($field, $value, $argMethod);
                break;

        }
    }

    protected function checkTypeFields($arr, $fieldName, $_value){
        foreach ($arr as $key => $valid) {
            switch ($valid) {
                case 'string':
                    $this->isString($fieldName, $_value);
                    break;
                case 'integer':
                    $this->isInteger($fieldName, $_value);
                    break;
                case 'json':
                    $this->isJson($fieldName, $_value);
                    break;
                case 'email':
                    $this->isEmail($fieldName, $_value);
                    break;
                case 'url':
                    $this->isUrl($fieldName, $_value);
                    break;
            }
        }
    }

    public function rules(array $rules = []) {
        $this->_rules = $rules;
        $this->installUserMessages();
    }

    public function setLanguage($lang = 'ru') {
        return $this->_lang = $this->existsLanguage($lang) ? $lang : false;
    }

    protected function existsLanguage($langName) {
        if (!array_key_exists($langName, $this->_languages))
            throw new \Exception('You chose a non-existent language');
        else
            return true;
    }

    protected function installUserMessages() {
        $rules = &$this->_rules;
        $messages = &$this->messagesFields;
        array_walk($rules, function ($val, $key) use (&$messages) {
            if (array_key_exists('message', $val)) {
                $messages[$key] = $val['message'];
            }
        });
    }

    protected function existsRequireFields($data) {
        if (!empty(array_column($this->_rules, 'require'))) {
            foreach ($this->_rules as $rule => $val) {
                if (array_key_exists('require', $val) && $val['require'] !== false) {
                    if (!array_key_exists($rule, $data)){
                        $this->error($rule, "require");
                    }
                }
            }
        }
    }

    protected function error($field, $key, $val = "") {
        $nowLang = $this->_languages[$this->_lang];
        if (array_key_exists($field, $this->messagesFields)) { // подставляем сообщение из этой переменной если есть
            $this->_errors[$field] = $this->messagesFields[$field];
            return true;
        } else {
            if (array_key_exists($key, $nowLang)) {
                $this->_errors[$field][] = sprintf($nowLang[$key], $val);
                return true;
            }
        }
        throw new Exception('Error');
    }

    protected function isString($field, $str) {
        if (is_string($str) === false) {
            $this->error($field, 'string');
            return false;
        }
        return true;
    }

    protected function isInteger($field, $str) {
        if (preg_match('/^([0-9]|-[1-9]|-?[1-9][0-9]*)$/i', $value) === false) {
            $this->error($field, 'integer');
            return false;
        }
        return true;
    }

    /**
     * Происходить проверка на количество символов, если $str < $minCount - то тогда ошибка
     * @param $field - это имя валидируемого поля, у нас ошибки выглядять вот так: 'имя_валидируемоего_поля' => [все ошибки которые произошли при валидации]
     * @param $str - строка у которой будет, вычислятся количество символов
     * @param $minCount - минимально количество символов, которое может принимать $str
     * @return mixed
     * @throws Exception
     */
    protected function minLength($field, $str, $minCount) {
        if ($this->length($str) < $minCount) {
            $this->error($field, 'minLength', $minCount);
            return false;
        }
        return true;
    }

    /**
     * Происходить проверка на количество символов, если $str > $maxCount - то тогда ошибка
     * @param $field - это имя валидируемого поля, у нас ошибки выглядять вот так: 'имя_валидируемоего_поля' => [все ошибки которые произошли при валидации]
     * @param $str - строка у которой будет, вычислятся количество символов
     * @param $maxCount - максимально количество символов, которое может принимать $str
     * @return mixed
     * @throws Exception
     */
    protected function maxLength($field, $str, $maxCount) {
        if ($this->length($str) > $maxCount ) {
            $this->error($field, 'maxLength', $maxCount);
            return false;
        }
        return true;
    }

    /**
     * Тут объяснять нечего - здесь вот такая проверка, if($int < $minInt) - то тогда ошибка
     * @param $field - это имя валидируемого поля, у нас ошибки выглядять вот так: 'имя_валидируемоего_поля' => [все ошибки которые произошли при валидации]
     * @param $int - пришедшее число
     * @param $minInt - минимально число которое может принимать $int, если $int < $maxInt - то тогда ошибка
     * @return mixed
     * @throws Exception
     */
    protected function min($field, $int, $minInt) {
        if ($int < $minInt) {
            $this->error($field, 'min', $minInt);
            return false;
        }
        return true;
    }

    /**
     * Тут объяснять нечего - сдесь вот такая проверка, if($int > $maxInt) - то тогда ошибка
     * @param $field - это имя валидируемого поля, у нас ошибки выглядять вот так: 'имя_валидируемоего_поля' => [все ошибки которые произошли при валидации]
     * @param $int - пришедшее число
     * @param $maxInt - максимально число которое можеть принимать $int, если $int > $maxInt - то тогда ошибка
     * @return mixed
     * @throws Exception
     */
    protected function max($field, $int, $maxInt) {
        if ($int > $maxInt) {
            $this->error($field, 'max', $maxInt);
            return false;
        }
        return true;
    }

    /**
     * Вычисляет количество символов в $str, и проверяет количество символов равняется ли $count, если нет ошибка
     * @param $field - это имя валидируемого поля, у нас ошибки выглядять вот так: 'имя_валидируемоего_поля' => [все ошибки которые произошли при валидации]
     * @param $str - по этой строке будет вычисляться количество символов
     * @param $count - количество символов которому должен соответствовать $str
     * @return mixed
     * @throws Exception
     */
    protected function fixedLength($field, $str, $count) {
        $this->error($field, 'length', $count);
        return false;
    }

    protected function length($str) {
        if (function_exists('mb_strlen')){
            return mb_strlen($str, 'UTF-8') ?: false;
        }
        return str_len($str) ?: false;
    }

    /**
         * Проверяет пришедший строку - является ли он json - строкой
         * @param $field - это имя валидируемого поля, у нас ошибки выглядять вот так: 'имя_валидируемоего_поля' => [все ошибки которые произошли при валидации]
         * @param $json - аргумент который, мы будем проверять является ли он json'ом
         * @return mixed
         * @throws Exception
         */
    protected function isJson($field, $json) {
        $jsonObject = json_decode($json);
        if(($jsonObject instanceof \stdClass) === false) {
            return $this->error($field, 'json');
        }
        return true;
    }

    /**
     * Проверяет пришедший строку - является ли он email
     * @param $field - это имя валидируемого поля, у нас ошибки выглядять вот так: 'имя_валидируемоего_поля' => [все ошибки которые произошли при валидации]
     * @param $email - аргумент который, мы будем проверять на валидность
     * @return mixed
     * @throws Exception
     */
    protected function isEmail($field, $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
            return $this->error($field, 'email');

        return true;
    }

    /**
     * Проверяет пришедший строку - является ли он url
     * @param $field - это имя валидируемого поля, у нас ошибки выглядять вот так: 'имя_валидируемоего_поля' => [все ошибки которые произошли при валидации]
     * @param $url - url который, мы будем проверять на валидность
     * @return mixed
     * @throws Exception
     */
    protected function isUrl($field, $url) {
        $checkProtocols = implode('|', $this->url);
        if (preg_match('#'.$checkProtocols.'#i', $url, $matches) == 0)
            $url = 'http://' . $url;

        $url = parse_url( $url);
        foreach ($url as $key => $val) {
            if ($key == 'host') {
                if (preg_match_all('#^[a-zA-Z0-9а-яА-ЯЁё]+\.[a-zA-Z0-9а-яА-ЯЁё]+$#u', $val) == 0)
                    return $this->error($field, 'url');
            } elseif ($key == 'path') {
                if (preg_match_all('#^([a-zA-Z0-9а-яА-ЯЁё_=/-]){0,}([a-zA-Z\.])*$#u', $val) == 0)
                    return $this->error($field, 'url');
            }
        }
        return true;

    }


    protected function dd($arr) {
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * @return array
     */
    public function getMessagesFields()
    {
        return $this->messagesFields;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->_languages;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->_rules;
    }

}

