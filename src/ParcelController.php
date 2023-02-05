<?php

class ParcelController
{
    public function __construct(private ParcelGateway $parcelGateway, private UserGateway $userGateway)
    {
    }

    public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            
            $this->processResourceRequest($method, $id);
            
        } else {
            
            $this->processCollectionRequest($method);
            
        }
    }

    private function processResourceRequest(string $method, string $id): void
    {
        $errors = $this->validateID($id);
                
        if ( ! empty($errors)) {
            http_response_code(422);
            echo json_encode(["errors" => $errors]);
            return;
        }

        $parcel = $this->parcelGateway->get($id);

        if(sizeof($parcel) == 1){
            http_response_code(404);
            echo json_encode(["message" => "Product not found"]);
            return;
        }else{
            switch ($method) {
                case "GET":
                    $parcel["user"] = $this->userGateway->get($parcel["user_id"]);
                    unset($parcel["user_id"]);
                    echo json_encode($parcel);
                    break;
                default:
                    http_response_code(405);
                    header("Allow: GET");
            }
        }
    }
    
    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            //implemented but not needed for the assignment
            //case "GET":
                //echo json_encode($this->parcelGateway->getAll());
                //break;
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                $errors = $this->getValidationErrors($data);
                
                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                
                $gatewayResponse = $this->parcelGateway->create($data);

                $gatewayResponse["user"] = $this->userGateway->get($gatewayResponse["user_id"]);
                unset($gatewayResponse["user_id"]);
                
                http_response_code(201);
                echo json_encode([
                    "id" => intval($gatewayResponse["id"]),
                    "parcel_number" => $gatewayResponse["parcel_number"],
                    "size"=> $gatewayResponse["size"],
                    "user" => $gatewayResponse["user"],
                ]);
                break;
            
            default:
                http_response_code(405);
                header("Allow: POST");
        }
    }

    private function validateID(string $id): array
    {
        $errors = [];

        if (filter_var($id, FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-f0-9]{10}+$/"]]) === false){
            $errors[] = "parcel_number must be of valid format";
        }

        return $errors;
    }

    //redundant check for existing
    private function getValidationErrors(array $data): array
    {
        $errors = [];
        
        if (empty($data["size"])) {
            $errors[] = "size is required";
        }else if (array_key_exists("size", $data)) {
            if (!in_array($data["size"],["S","M","L","XL"])){
                $errors[] = "size must be element of [S,M,L,XL]";
            }
        }

        if (empty($data["user_id"])) {
            $errors[] = "user_id is required";
        }else if (array_key_exists("user_id", $data)) {
            if (filter_var($data["user_id"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "user_id must be an integer";
            }
            if (sizeof($this->userGateway->get($data["user_id"])) == 1){
                $errors[] = "user_id must be an existing user";
            }
        }
        
        return $errors;
    }
}