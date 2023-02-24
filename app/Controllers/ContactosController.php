<?php

namespace PruebaAPI\Controllers;

use PruebaAPI\Models\ContactosGateway;

class ContactosController
{

  private $db;
  private $requestMethod;
  private $userId;

  private $contactosGateway;

  public function __construct($db, $requestMethod, $userId)
  {
    $this->db = $db;
    $this->requestMethod = $requestMethod;
    $this->userId = $userId;

    $this->contactosGateway = new ContactosGateway($db);
  }

  public function processRequest()
  {
    switch ($this->requestMethod) {
        // case 'GET':
        //   if ($this->userId) {
        //     $response = $this->getUser($this->userId);
        //   } else {
        //     $response = $this->getAllUsers();
        //   };
        //   break;
      case 'POST':
        switch ($this->userId) {
          case 'GET':
            $response = $this->getUser();
            break;
          case 'GETALL':
            $response = $this->getAllUsers();
            break;
          case null:
            $response = $this->createUserFromRequest();
            break;
          default:
            $response = $this->notFoundResponse();
            break;
        }
        break;
      case 'PUT':
        $response = $this->updateUserFromRequest();
        break;
      case 'DELETE':
        $response = $this->deleteUser();
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

  private function getAllUsers()
  {
    $result = $this->contactosGateway->findAll();
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function getUser()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    $id = $input['id'];
    $result = $this->contactosGateway->find($id);
    if (!$result) {
      return $this->notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function createUserFromRequest()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (!$this->validatePerson($input)) {
      return $this->unprocessableEntityResponse();
    }
    $this->contactosGateway->insert($input);
    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = null;
    return $response;
  }

  private function updateUserFromRequest()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    $id = $input['id'];
    $result = $this->contactosGateway->find($id);
    if (!$result) {
      return $this->notFoundResponse();
    }
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (!$this->validatePerson($input)) {
      return $this->unprocessableEntityResponse();
    }
    $this->contactosGateway->update($id, $input);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = null;
    return $response;
  }

  private function deleteUser()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    $id = $input['id'];
    $result = $this->contactosGateway->find($id);
    if (!$result) {
      return $this->notFoundResponse();
    }
    $this->contactosGateway->delete($id);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = null;
    return $response;
  }

  private function validatePerson($input)
  {
    if (!isset($input['nombre'])) {
      return false;
    }
    if (!isset($input['telefono'])) {
      return false;
    }
    if (!isset($input['email'])) {
      return false;
    }
    return true;
  }

  private function unprocessableEntityResponse()
  {
    $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
    $response['body'] = json_encode([
      'error' => 'Invalid input'
    ]);
    return $response;
  }

  private function notFoundResponse()
  {
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = null;
    return $response;
  }
}
