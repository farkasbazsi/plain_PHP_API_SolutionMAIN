<?php

class UserGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT id, first_name, last_name, email_address, phone_number
                FROM users";
                
        $stmt = $this->conn->query($sql);

        $stmt->execute();
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $data;
    }

    public function create(array $data): string
    {
        $stmt = $this->conn->prepare("INSERT INTO users 
                (first_name, last_name, password, email_address, phone_number)
                VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data["first_name"], 
                        $data["last_name"], 
                        password_hash($data["password"],PASSWORD_BCRYPT), 
                        $data["email_address"], 
                        ($data["phone_number"] != null)? $data["phone_number"]:null]);
        
        return $this->conn->lastInsertId();
    }

    public function get(string $id): array | false
    {
        $stmt = $this->conn->prepare('SELECT id, first_name, last_name, email_address, phone_number FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $data = (array) $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }
}