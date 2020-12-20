<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LoginUser extends MY_Controller {

	public function __construct()
    {
		parent::__construct();
		$this->load->model('perfil_model', 'PerfilModel');
    }
	
	
	
	public function index() {
        $_POST = $this->security->xss_clean($_POST);
		$email = $_POST['email'];
		$pass = $_POST['password'];

        if(empty($email) || empty($pass)) {
            return $this->output_json(400 ,'Debe completar todos los campos');
        }
        $userDB = $this->UserModel->login($email, $pass);
        if (empty($userDB))
            {
                $this->data = [
                    'detalle' => "Email o password incorrectos"
                ];
                return $this->output_json(404 , 'No se encontro usuario',$this->data);
            } 
            
            $token_data['id'] = $userDB['ID_US'];
            $token_data['nombres'] = $userDB['NOMBRES'];
            $token_data['apellidos'] = $userDB['APELLIDO_PATERNO'];
            $token_data['email'] = $userDB['EMAIL'];
            $token_data['telefono'] = $userDB['TELEFONO'];
            $token_data['rol'] = $userDB['TIPO'];
            $token_data['time'] = time();

            $user_token = AUTHORIZATION::generateToken($token_data);
            $this->data = [
                'user_id' => $userDB['ID_US'],
                'nombres' => trim($userDB['NOMBRES']),
                'apellidos' => trim($userDB['APELLIDO_PATERNO']).' '.trim($userDB['APELLIDO_MATERNO']),
                'rol' => $userDB['TIPO'],
                'token' => $user_token,
            ];

            $this->output_json(200 , 'login success',$this->data);
	}
	
}
