<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

	public function __construct()
    {
	    	parent::__construct();
	      $this->load->model('User_Model', 'UserModel');
        $this->load->model('Perfil_Model', 'PerfilModel');
    }
	
	/**
     * User Register
     * --------------------------
     * @param: fullname
     * @param: username
     * @param: email
     * @param: password
     * --------------------------
     * @method : POST
     * @link : /register/
     */
	
	
    public function get($id) : CI_Output
    {
      if (!(int)$id)    return $this->output_json(400 ,'param id is required'); 
      $userDB = $this->UserModel->get($id);
      if( empty($userDB) ) return $this->output_json(200 , 'no se encontro user con el id' );
      return $this->output_json(200 , 'usuario encontrado', $userDB);
    }
    public function update()
    {
     
    }
    public function remove()
    {
     
    }
    public function getAll() : CI_Output
    {
      $usersDB = $this->UserModel->getAll();
      if( empty($usersDB) ) return $this->output_json( 200 , `No existen usuarios`);
     
      return $this->output_json(200 , 'all user find !!', $usersDB);
    }
	
}
