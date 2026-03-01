<?php

namespace app\Core\db;

use app\Core\Application;
use app\Core\Model;

abstract class DbModel extends Model
{

   abstract static public function TableName(): string;

   abstract public function attributes(): array;
   abstract public function primaryKey(): string;

   public function save(){
      $tableName = $this->TableName();
      $attributes = $this->attributes();
      $param = array_map(fn($attri) => ":$attri", $attributes);
      $statement = self::prepare("INSERT INTO $tableName (".implode(','.$attributes).") VALUES(".implode(','.$param).")");
      foreach($attributes as $attribute){
         $statement->bindValue(":$attribute",$this->{$attribute});
      }
      $statement->execute();

      return true;
   }

   public static function findOne($where){
      $tableName = static::TableName();
      $attributes = array_keys($where);
      $sql = implode("AND",array_map(fn($attr)=>"$attr = :$attr", $attributes));
      $statment = self::prepare("SELECT * FROM $tableName WHERE $sql");
      foreach($where as $key => $value){
         $statment->bindValue(":$key", $value);
      }
      $statment->execute();
      return $statment->fetchObject(static::class);
      }

   public static function prepare($sql){
      return Application::$app->db->pdo->prepare($sql);
   }

}