<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Privileges extends MY_Controller {

	public function __construct()
    {
        parent::__construct();
		$this->load->model('privileges_model', 'PrivilegesModel');
    }
	
	public function index() {
        
        $privileges = $this->PrivilegesModel->getAll();
        if( !$privileges ) return $this->output_json(200,'No se encontraron resultados',[] , false);
        return $this->output_json(200 , 'perfiles find !!' , $privileges);
           
	}
	
}
