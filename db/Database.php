<?php

namespace app\Core\db;

use app\Core\Application;
use PDO;

class Database
{
    public PDO $pdo;
    public function __construct(array $config = [])
    {
        $domainServiceName = $config['dsn'] ?? '';
        $username = $config['user'] ??  '';
        $password = $config['password'] ??  '';
        // var_dump($domainServiceName, $username, $password);
        // exit;
        $this->pdo = new PDO($domainServiceName, $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function applyingMigration()
    {
        $this->CreateMigrationsTable();
        $applied = $this->getAppliedMigration();

        $newMigrations = [];
        $files = scandir(Application::$ROOT_DIR . '/migrations');

        $toArrayMigrations =  array_diff($files, $applied);
        foreach ($toArrayMigrations as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }
            require_once Application::$ROOT_DIR . '/migrations/' . $migration;
            $className =  pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $className();
             $this->log("Applying migration $migration".PHP_EOL);
            $instance->up();
             $this->log("Applied migration $migration".PHP_EOL);
            $newMigrations[] = $migration;
        }
        if(!empty($newMigrations)){
            $this->saveMigrations($newMigrations);
        }else{
           $this->log("All Migrations are applied");
        }
    }

    public function CreateMigrationsTable()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY  KEY,
        migrations VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )ENGINE=INNODB;");
    }

    public function getAppliedMigration()
    {
        $statement = $this->pdo->prepare("SELECT migrations FROM migrations");
        $statement->execute();

        return $statement->fetchALl(PDO::FETCH_COLUMN);
    }

    public function prepare($sql){
        return $this->pdo->prepare($sql);
    }

    public function saveMigrations(array $migrations){
        $str = implode(",",array_map(fn($m)=>"('$m')",$migrations));
        $statment = $this->pdo->prepare("INSERT INTO migrations (migrations) VALUES 
            $str
        " );

        $statment->execute();
    }

    protected function log($message){
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }
}
