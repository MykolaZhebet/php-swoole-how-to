<?php

namespace App\Models;

use App\Infrastructure\DbAdapter;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
    protected $connection = 'default';
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function createTable() {
        $sql = <<<SQL
            CREATE TABLE users (
                id INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );
        SQL;
        return DbAdapter::execute($sql);
    }

    public function findOld(int $id) {
        $sql = <<<SQL
            SELECT * FROM users WHERE id = :id;
        SQL;
        return DbAdapter::fetchAll($sql, ['id' => $id]);
    }

    public function get($field, $value) {
        $sql = <<<SQL
            SELECT * FROM users WHERE $field = :$field;
        SQL;
        return DbAdapter::fetchAll($sql, [':' . $field => $value]);
    }

    public function insert(array $data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql = <<<SQL
            INSERT INTO users (name, email, password) VALUES (:name, :email, :password)
        SQL;
        return DbAdapter::execute($sql, $data);
    }
}
