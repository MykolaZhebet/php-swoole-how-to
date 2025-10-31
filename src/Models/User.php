<?php

namespace App\Models;

use App\Infrastructure\DbAdapter;
use App\Models\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model {
    use HasFactory;
    protected $connection = 'default';
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected static function newFactory() {
        return new UserFactory();
    }

    public function tokens() {
        return $this->hasMany(Token::class, 'user_id');
    }

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
