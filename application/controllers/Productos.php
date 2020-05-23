<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\libraries\REST_Controller;


class Productos extends REST_Controller {


  public function __construct(){

    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
    header("Access-Control-Allow-Origin: *");


    parent::__construct();
    $this->load->database();

  }

  public function obtenerTodos_get( $pagina = 0 ){
    $query = $this->db->query('SELECT * FROM `productos` limit '.  $pagina * 10 .',10');
    $respuesta = [ 'error' => FALSE, 'mensaje' => $query->result_array()];
    $this->response( $respuesta );
  }

  public function obtenerPorCodigo_get( $codigo = '' ){
    $query = $this->db->query('SELECT * FROM `productos` where codigo = "'. $tipo .'"');
    $respuesta = ['error' => FALSE, 'mensaje' => $query->result_array()];
    $this->response( $respuesta );

  }

  public function obtenerPorTipo_get( $tipo=0, $pagina =0 ){
    if($tipo == 0){
      $respuesta = ['error' => TRUE,'mensaje' => 'Falta el parámetro de tipo'];
      $this->response( $respuesta, REST_Controller::HTTP_BAD_REQUEST );
      return;
    }

    $query = $this->db->query('SELECT * FROM `productos` where linea_id = '. $tipo .' limit '. $pagina * 10 .',10');
    $respuesta = ['error' => FALSE, 'mensaje' => $query->result_array()];
    $this->response( $respuesta );
  }

  public function buscar_get( $termino = ''){
    if (strlen($termino) > 2) {
        $query = $this->db->query('SELECT * FROM `productos` where producto LIKE "%'.  $termino . '%"');
        $respuesta = [ 'error' => FALSE, 'mensaje' => $query->result_array()];
        $this->response( $respuesta );
    } 
  }
}
