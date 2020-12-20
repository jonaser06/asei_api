<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller {
	
	private function test()
	{
		return $this->response;
	}
	public function index()
	{
		$usersDB = [
			['PERFIL' => 'ADMIN'    	, 'DESCRIPTION' => 'ESTE ES UN USER DE ADMIN'],
			['PERFIL' => 'ASOCIADO'   	, 'DESCRIPTION' => 'ESTE ES UN USER DE ASOCIADO'],
			['PERFIL' => 'COLABORADOR'  ,'DESCRIPTION' => 'ESTE ES UN USER DE COLABORADOR'],
		];
		
		$this->output_json(400 , 'En welcome');
		
	}
}
