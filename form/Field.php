<?php

namespace app\Core\form;

use app\Core\Model;

class Field extends BaseField
{

  public const TYPE_TEXT = "text";
  public const TYPE_TEXTAREA = "textarea";
  public const TYPE_RADIO = "radio";
  public const TYPE_CHECKBOX = "checkbox";
  public const PASSWORD = "password";

  public string $type;

  public function __construct(Model $model, string $attributes)
  {
    $this->type = self::TYPE_TEXT;
    parent::__construct($model, $attributes);
  }



  public function password()
  {
    return $this->type = self::PASSWORD;
    return $this;
  }

  public function renderInput(): string
  {
    return sprintf(
      '<input type="%s" value="%s" class="form-control%s" name="%s">',
      $this->type,
      $this->attribute,
      $this->model->{$this->attribute},
      $this->model->hasError($this->attribute) ? ' is-invalid' : '',
    );
  }
}
