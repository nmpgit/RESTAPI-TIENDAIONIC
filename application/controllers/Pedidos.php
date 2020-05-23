<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once( APPPATH.'/libraries/REST_Controller.php' );
use Restserver\libraries\REST_Controller;


class Pedidos extends REST_Controller {


	public function __construct(){

	    header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding");
		header("Access-Control-Allow-Origin: *");


		parent::__construct();
		$this->load->database();

	}

	public function realizarPedido_post($usuario = '0') {

		$data = $this->post();
		
		if (strlen($data['items']) > 0) {

			$this->db->reset_query();
	      	$insertar = ['usuario_id' => $usuario];
	      	$this->db->insert('ordenes', $insertar);
	      	$ordenId = $this->db->insert_id();
	      	$this->response($ordenId);
			$this->db->reset_query();
	      	$items = explode(',', $data['items']);

	      	for ($i=0; $i < count($items); $i++) { 
				$dataInsertar = ['producto_id' => $items[$i], 'orden_id' => $ordenId];
				$this->db->insert('ordenes_detalle', $dataInsertar);
	      	}

			$respuesta = [ 'error' => FALSE, 'mensaje'=> 'Orden generada.'];
	      	$this->response($respuesta);      		


      	} else {
      		$respuesta = [ 'error' => TRUE, 'mensaje'=> 'Faltan enviar ítems'];
      		$this->response( $respuesta, REST_Controller::HTTP_BAD_REQUEST );
		}
		

	}
	
	
	public function obtenerPedidos_get ($usuario = '0') {
		$this->validarUsuario($usuario);
		$this->db->reset_query();
		$query = $this->db->query('SELECT * FROM `ordenes` where usuario_id = '. $usuario);
		
		$ordenes = [];

		foreach ($query->result() as $row) {
			$query_detalle = $this->db->query('SELECT a.orden_id, b.* FROM `ordenes_detalle` a INNER JOIN `productos` b on a.producto_id = b.codigo where a.orden_id = '. $row->id);

			$orden = ['id' => $row->id, 'creado_en' => $row->creado_en, 'detalle' => $query_detalle->result()];

			array_push($ordenes, $orden);
		}	
			$respuesta = [ 'error' => FALSE, 'mensaje'=> $ordenes];
			$this->response($respuesta);

	}

	public function borrarPedido_delete($usuario = '0', $orden = '0') {
		$this->validarUsuario($usuario);

		if($orden == '0'){		
			$respuesta = [ 'error' => TRUE, 'mensaje'=> 'Órden inválida'];
      		$this->response( $respuesta, REST_Controller::HTTP_BAD_REQUEST );
		}

		//verifico si la orden es del usuario.
		$this->db->reset_query();
		$condiciones = ['id' => $orden, 'usuario_id' => $usuario];
		$this->db->where($condiciones);
		$query = $this->db->get('ordenes');
		$ordenCorrespondeUsuario = $query->row();

		if (!$ordenCorrespondeUsuario) {
			$respuesta = [ 'error' => TRUE, 'mensaje'=> 'La órden no corresponde al usuario'];
			$this->response( $respuesta, REST_Controller::HTTP_BAD_REQUEST );      		
			return;
		}

		$condiciones = ['id' => $orden];
		$this->db->delete('ordenes', $condiciones);
		$condiciones = ['orden_id' => $orden];
		$this->db->delete('ordenes_detalle', $condiciones);

		$respuesta = [ 'error' => FALSE, 'mensaje'=> 'Órden eliminada'];
		$this->response( $respuesta );


	}
	
	public function validarUsuario($usuario) {
		if($usuario == '0'){		
			$respuesta = [ 'error' => TRUE, 'mensaje'=> 'Usuario inválido'];
      		$this->response( $respuesta, REST_Controller::HTTP_UNAUTHORIZED );
		}
		//hay dato de usuario, busco en la base si son correctos.
		$condiciones = ['id' => $usuario];
		$this->db->where($condiciones);
		$query = $this->db->get('login');
		$existe = $query->row();

		if (!$existe) {
			$respuesta = [ 'error' => TRUE, 'mensaje'=> 'Usuario incorrectos'];
			$this->response( $respuesta, REST_Controller::HTTP_UNAUTHORIZED );      		
			return;
		}
	}
}

