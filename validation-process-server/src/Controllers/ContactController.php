<?php

namespace Src\Controllers;


use Src\Gateways\ContactGateway;

class ContactController
{
    /**
     * @var
     */
    private $requestMethod;
    private $contactId;
    private $contactGateway;

    /**
     * @param $requestMethod
     * @param $contactId
     */
    public function __construct($requestMethod, $contactId)
    {
        $this->contactGateway = ContactGateway::getInstance();
        $this->requestMethod = $requestMethod;
        $this->contactId = $contactId;
    }

    /**
     * switch and detect method and act accordingly
     * @return void
     */
    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->contactId) {
                    $response = $this->getContact($this->contactId);
                } else {
                    $response = $this->getAllContacts();
                };
                break;
            case 'POST':
                $response = $this->createContactFromRequest();
                break;
            case 'PUT':
                $response = $this->updateContactFromRequest($this->contactId);
                break;
            case 'DELETE':
                $response = $this->deleteContact($this->contactId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    /**
     * @return array
     */
    private function getAllContacts()
    {
        $result = $this->contactGateway->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getContact($id)
    {
        $result = $this->contactGateway->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createContactFromRequest()
    {
        $input = (array)json_decode(file_get_contents('php://input'), TRUE);
        $validate = $this->validateContact($input);
        if (!$validate->status) {
            return $this->unprocessableEntityResponse($validate);
        }
        $insert = $this->contactGateway->insert($input);
        $response['status_code_header'] = $insert ? 'HTTP/1.1 201 Created' : 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            "status" => $insert,
            "data" => $input]);
        return $response;
    }

    private function updateContactFromRequest($id)
    {
        $result = $this->contactGateway->find($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $input = (array)json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateContact($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->contactGateway->update($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function deleteContact($data)
    {
        $result = $this->contactGateway->find($data);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $result = $this->contactGateway->delete($data);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function validateContact($input)
    {
        $error = [];
        $status = true;
        foreach ($input as $key => $value) {
            if (!isset($value) || $value == '') {
                $error[$key] = "empty or invalid " . $key;
                $status = false;
            } else if ($key == 'name') {
                if (!preg_match("/^[a-zA-Z-' ]*$/", $value)) {
                    $error[$key] = "Only letters and white space allowed for " . $key;
                }
            } else if ($key == 'email') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $error[$key] = "Invalid " . $key . " format ";
                }
            } elseif ($key == 'phone') {
                if (!is_numeric($value)) {
                    $error[$key] = $key . " is not in a valid format";
                }
            }
        }
        return (object)array("status" => $status, "error" => $error);
    }

    private function unprocessableEntityResponse($validate)
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode($validate);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }
}
