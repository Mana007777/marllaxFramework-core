<?php

namespace app\Core;

abstract class Model
{
    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';
    public const RULE_UNIQUE = 'unique';

    public function loadData($data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    abstract public function rules();

    public array $errors = [];

    public function label(): array
    {
        return [];
    }

    public function getLabel($attrib)
    {
        return $this->label[$attrib] ?? $attrib;
    }
    public function validate()
    {

        foreach ($this->rules() as $attrib => $rule) {
            $value = $this->{$attrib};
            foreach ($rule as $rules) {
                $ruleName = $rules;
                if (!is_string($ruleName)) {
                    $ruleName = $rules[0];
                }
                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addError($attrib, self::RULE_REQUIRED);
                }
                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($attrib, self::RULE_EMAIL);
                }
                if ($ruleName === self::RULE_MIN && strlen($value) < $rules['min']) {
                    $this->addError($attrib, self::RULE_MIN, ['min' => $rules['min']]);
                }
                if ($ruleName === self::RULE_MAX && strlen($value) > $rules['max']) {
                    $this->addError($attrib, self::RULE_MAX, ['max' => $rules['max']]);
                }
                if ($ruleName === self::RULE_MATCH && $value !== $this->{$rules['match']}) {
                    $this->addError($attrib, self::RULE_MATCH, ['match' => $rules['match']]);
                }
                if ($ruleName === self::RULE_UNIQUE) {
                    $className = $rule['class'];
                    $uniqueAttrib = $rule['attribute'] ?? $attrib;
                    $tableName = $className::tableName();
                    $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $uniqueAttrib = :attr");
                    $statement->bindValue(":attr", $value);
                    $statement->execute();
                    $record = $statement->fetchObject();
                    if ($record) {
                    }
                    $this->addError($attrib, self::RULE_UNIQUE, ['field' => $this->getLabel($attrib)]);
                }
            }
        }
        return empty($this->errors);
    }

    public function addError(string $attrib, string $rule, $params = [])
    {
        $message = $this->errorMessages()[$rule] ?? '';
        foreach ($params as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }
        $this->errors[$attrib][] = $message;
    }

    public function ErrorMesg(string $attrib, string $message){
        $this->errors[$attrib][] = $message;
    }

    public function errorMessages(): array
    {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_EMAIL => 'This field must be a valid email address',
            self::RULE_MIN => 'Min length of this field must be {min}',
            self::RULE_MAX => 'Max length of this field must be {max}',
            self::RULE_MATCH => 'This field must be the same as {match}',
            self::RULE_UNIQUE => 'Record with this {field} already exists'
        ];
    }

    public function hasError($attrib)
    {
        return $this->errors[$attrib] ?? false;
    }

    public function getFirstErrors($attrib): array
    {
        return $this->errors[$attrib][0] ?? false;
    }
}
