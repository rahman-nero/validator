<?php
namespace Validator;

final class Validator
{
    private $_rules = [];
    private $_fields = [];
    private $_languages = [];
    private $_requiredFields = [];
    private $_lang;
    private $_errors = [];
    private $messagesFields = [];

    public function __construct(array $rules = [], $_lang = 'ru') {
        $this->_languages = require __DIR__ . '/languages/language.php';
        $this->rules($rules);
        $this->setLanguage($_lang);
    }

    public function validate(array $data) {
        $data = $this->existsRequireFields($data);
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

    protected function error($field, $stringKey, $val = '') {
        $nowLang = $this->_languages[$this->_lang];
        if (array_key_exists($stringKey, $nowLang)) {
            $this->_errors[$field][] = sprintf($nowLang[$stringKey], $val);
            return true;
        }
        throw new Exception('Error');
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

