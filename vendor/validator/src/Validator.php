<?php
namespace Validator;

class Validator
{
    private $_rules = [];
    private $_fields = [];
    private $_languages = [];
    private $_lang;
    private $_errors = [];
    private $messagesFields = [];

    public function __construct(array $rules = [], $_lang = 'ru') {
        $this->languages = require __DIR__ . '/languages/language.php';
        $this->rules($rules);
        $this->setLanguage($_lang);
    }

    public function validate(array $data) {

    }

    public function rules(array $rules = []) {
        $this->_rules = $rules;
        $this->installUserMessages();
    }

    public function setLanguage($lang = 'ru') {
        return $this->_lang = $this->existsLanguage($lang) ? $lang : false;
    }

    protected function existsLanguage($langName) {
        if (array_key_exists($langName, $this->_languages))
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

