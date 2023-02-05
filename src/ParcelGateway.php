<?php

class ParcelGateway
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    //implemented but not needed for the assignment
    /*public function getAll(): array
    {
        $sql = "SELECT *
                FROM parcels";
                
        $stmt = $this->conn->query($sql);

        $stmt->execute();
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $data;
    }*/

    public function get(string $id): array | false
    {
        $stmt = $this->conn->prepare('SELECT id, parcel_number, size, user_id FROM parcels WHERE parcel_number = ?');
        $stmt->execute([$id]);
        $data = (array) $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function create(array $data): array
    {
        do{
            //GENERATING PARCEL NUMBER
            $parcel_number = implode( array_map( function() { return dechex( mt_rand( 0, 15 ) ); }, array_fill( 0, 10, null ) ) );
            
            //CHECK IF PARCEL NUMBER EXISTS
            $stmt = $this->conn->prepare("SELECT * FROM parcels WHERE parcel_number = :parcel_id");
            $stmt->bindParam(':parcel_id', $parcel_number, PDO::PARAM_STR);
            $stmt->execute();
          }while($stmt->rowCount() > 0);

        $stmt = $this->conn->prepare("INSERT INTO parcels 
                        (parcel_number, size, user_id) 
                        VALUES (?,?,?)");
        $stmt->execute([$parcel_number, $data["size"], $data["user_id"]]);
        
        return ["id" => $this->conn->lastInsertId(), "parcel_number" => $parcel_number, 
                    "size" => $data["size"], "user_id" => $data["user_id"]];
    }

}