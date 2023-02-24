<?php

namespace PruebaAPI\Models;

use \Firebase\JWT\JWT;

class UsuariosGateway
{

  private $db = null;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function login($usuario, $password)
  {
    $statement = "SELECT * FROM usuarios WHERE usuario = ? AND password = ?";

    try {
      $statement = $this->db->prepare($statement);
      $statement->execute(array($usuario, $password));
      $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }

  public function crearToken($usuario)
  {
    $key = '1234';
    $token = array(
      "iat" => 1356999524,
      "nbf" => 1357000000,
      "usuario" => $usuario
    );
    $jwt = JWT::encode($token, $key, 'HS256');
    return $jwt;
  }
}
