<?php

namespace app\Core;

use app\Core\db\DbModel;

abstract class UserModel extends DbModel{

  abstract public function getDisplayName(): string;
}