<?php

class UserController
{
    public function __construct(private UserGateway $gateway)
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
        http_response_code(405);
        header("No /{id} allowed");
    }
    
    private function processCollectionRequest(string $method): void
    {
        switch ($method) {
            case "GET":
                echo json_encode($this->gateway->getAll());
                break;
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);
                
                $errors = $this->getValidationErrors($data);
                
                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }
                
                $id = $this->gateway->create($data);
                
                http_response_code(201);
                echo json_encode([
                    "id" => intval($id),
                    "first_name" => $data["first_name"],
                    "last_name"=> $data["last_name"],
                    "email_address" => $data["email_address"],
                    "phone_number" => $data["phone_number"]
                ]);
                break;
            
            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }

    //redundant check for exsting
    private function getValidationErrors(array $data): array
    {
        $errors = [];
        
        //posted id is validated, but not used in creation
        if (empty($data["id"])) {
            $errors[] = "id is required";
        }else if (array_key_exists("id", $data)) {
            if (filter_var($data["id"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "id must be an integer";
            }
        }

        if (empty($data["first_name"])) {
            $errors[] = "first_name is required";
        }else if (array_key_exists("first_name", $data)) {
            if (filter_var($data["first_name"], FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-zA-Z]+$/"]]) === false) {
                $errors[] = "first_name must be a string";
            }
        }

        if (empty($data["last_name"])) {
            $errors[] = "last_name is required";
        }else if (array_key_exists("last_name", $data)) {
            if (filter_var($data["last_name"], FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-zA-Z]+$/"]]) === false) {
                $errors[] = "last_name must be a string";
            }
        }

        if (empty($data["password"])) {
            $errors[] = "password is required";
        }else if (array_key_exists("password", $data)) {
            if (filter_var($data["password"], FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-zA-Z0-9]+$/"]]) === false) {
                $errors[] = "password must be a string";
            }
        }

        if (empty($data["email_address"])) {
            $errors[] = "email_address is required";
        }else if (array_key_exists("email_address", $data)) {
            if (filter_var($data["email_address"], FILTER_VALIDATE_EMAIL) === false) {
                $errors[] = "email_address must be an email address";
            }
        }
        
        $phone_number_regex="/^\+36[0-9]{9}+$/";
        if (array_key_exists("phone_number", $data)) {
            if (filter_var($data["phone_number"], FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => $phone_number_regex]]) === false) {
                $errors[] = "phone_number must be an integer";
            }
        }
        
        return $errors;
    }
}