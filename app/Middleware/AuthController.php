<?php

namespace PruebaAPI\Middleware;

use PruebaAPI\Models\UsuariosGateway;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{

  private $db;
  private $requestMethod;

  private $usuarioGateway;

  public function __construct($db, $requestMethod)
  {
    $this->db = $db;
    $this->requestMethod = $requestMethod;

    $this->usuarioGateway = new UsuariosGateway($db);
  }

  public function processRequest()
  {
    switch ($this->requestMethod) {
      case 'POST':
        $response = $this->login();
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

  private function login()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (!$this->validateLogin($input)) {
      return $this->unprocessableEntityResponse();
    }
    $result = $this->usuarioGateway->login($input['usuario'], $input['password']);
    if (!$result) {
      return $this->notFoundResponse();
    }
    $jwt = $this->usuarioGateway->crearToken($result);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($jwt);
    return $response;
  }

  private function validateLogin($input)
  {
    if (!isset($input['usuario'])) {
      return false;
    }
    if (!isset($input['password'])) {
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

  public function validarToken()
  {
    $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    if (isset($input['token'])) {
      $token = $input['token'];
      $key = '1234';
      try {
        $decoded = JWT::decode($token, new Key($key, 'HS256'));
        return true;
      } catch (\Exception $e) {
        return false;
      }
    } else {
      return false;
    }
  }
}
