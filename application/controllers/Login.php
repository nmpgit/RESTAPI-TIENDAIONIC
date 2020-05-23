<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\libraries\REST_Controller;


class Login extends REST_Controller {


  public function __construct(){

    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");


    parent::__construct();
    $this->load->database();

  }

  public function registrarse_post() {
    $data = $this->post();

    if(!isset($data['correo']) || !isset($data['contraseña'])){
      $respuesta = [ 'error' => TRUE, 'mensaje'=> 'Faltan campos', 'datos' => $data];
      $this->response( $respuesta, REST_Controller::HTTP_BAD_REQUEST );
      return;
    }

    // Tenemos correo y contraseña en un post

    //existe???
    $condiciones = ['correo' => $data['correo']];
    $query = $this->db->get_where('login', $condiciones);
    $usuario = $query->row();
 
    if(isset($usuario->id)) {
      $respuesta = [ 'error' => TRUE, 'mensaje'=> 'Correo existente'];
      $this->response( $respuesta);
    } else {
      $token = hash('ripemd160', $data['correo']);
      $dataInsertar = ['correo' => $data['correo'],'token' => $token, 'contraseña'=>$data['contraseña']];
      $this->db->insert('login', $dataInsertar);
      $respuesta = [ 'error' => FALSE, 'mensaje'=> 'Usuario generado.'];
      $this->response($respuesta);          
    }

  }


  public function index_post(){

    $data = $this->post();

    if(!isset($data['correo']) || !isset($data['contraseña'])){
      $respuesta = [ 'error' => TRUE, 'mensaje'=> 'La información enviada no es válida', 'datos' => $data];
      $this->response( $respuesta, REST_Controller::HTTP_BAD_REQUEST );
      return;
    }

    // Tenemos correo y contraseña en un post
    $condiciones = ['correo' => $data['correo'],'contraseña'=>$data['contraseña']];
    $query = $this->db->get_where('login', $condiciones);

    $usuario = $query->row();
    if( !isset( $usuario ) ){
      $respuesta = [ 'error' => TRUE, 'mensaje'=> 'Usuario y/o contraseña no son validos'];
      $this->response( $respuesta );
      return;
    }

    // AQUI!, tenemos un usuario y contraseña

    // TOKEN
    // $token = bin2hex( openssl_random_pseudo_bytes(20)  );
    $token = hash('ripemd160', $data['correo']);
    // Guardar en base de datos el token
    $this->db->reset_query();
    $actualizar_token = array('token' => $token);
    $this->db->where('id', $usuario->id);
    $hecho = $this->db->update('login', $actualizar_token);
    $respuesta = ['error' => FALSE, 'mensaje' => $usuario->id];
    $this->response( $respuesta );

  }



}
