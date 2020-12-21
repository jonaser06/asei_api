<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perfiles extends MY_Controller {

	public function __construct()
    {
        parent::__construct();
		$this->load->model('perfil_model', 'PerfilModel');
    }
	
	public function index() {
        
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);
            if ($decodedToken != false) {
               
                $perfiles = $this->PerfilModel->get();
                return $perfiles 
                ? $this->output_json(200,'perfiles',$perfiles)
                : $this->output_json(400,'no existen resultados');
            }
        }

        $this->output_json(401,'No tiene permisos para acceder');
           
        
	}
	
}
