<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LoginUser extends MY_Controller {

	public function __construct()
    {
		parent::__construct();
		$this->load->model('User_Model', 'UserModel');
        $this->load->model('Perfil_Model', 'PerfilModel');
        $this->load->model('Privileges_Model', 'PrivilegesModel');
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
	
	public function register()
	{
        $_POST = $this->security->xss_clean($_POST);
        
        if( $this->input->post('nombres', TRUE) == '' ||
            $this->input->post('ap_pat', TRUE)== '' ||
            $this->input->post('telefono', TRUE)== '' ||
            $this->input->post('email', TRUE)== '' ||
            $this->input->post('password', TRUE)== '' ||
            $this->input->post('perfil', TRUE)==''):
        return $this->output_json(400,'complete los campos');
        endif;
    
        $response = $this->UserModel->getOne(['EMAIL' => $this->input->post('email', TRUE)]);
        if($response):
            return $this->output_json(400,'El correo ya esta registrado , pruebe con otro');
        endif;

        $perfil = $this->PerfilModel->get(['TIPO' => $this->input->post('perfil', TRUE)]);
        if(!$perfil):
            return $this->output_json(400,'no existe el perfil , pruebe con otro');
        endif;
        
        $insert_data = [
            'ID_US'   => $this->generateId(),
            'NOMBRES' => $this->input->post('nombres', TRUE),
            'APELLIDO_PATERNO' => $this->input->post('ap_pat', TRUE),
            'APELLIDO_MATERNO' => $this->input->post('ap_mat', TRUE),
            'CLAVE' => md5($this->input->post('password', TRUE)),
            'TELEFONO' => $this->input->post('telefono', TRUE),
            'EMAIL' =>$this->input->post('email', TRUE), 
            'ID_PE' =>(int)$perfil['ID_PE'], 
            'ID_UB' => 1,
        ];
         
        $this->UserModel->insert_user($insert_data);
        $userDB = $this->UserModel->getOne(['ID_US' => $insert_data['ID_US']]);
        $this->data = [
            'id' => $userDB['ID_US']
        ];
        $this->output_json(201,'Regístro exitoso',$this->data);
        
	}
	public function login() {
        
        $_POST = $this->security->xss_clean($_POST);
        if(!array_key_exists('email',$_POST) || !array_key_exists('email',$_POST)) {
            return $this->output_json( 400 ,'email y password necesarios');
        }
		$email = $_POST['email'];
        $pass =  $_POST['password'];
       
        if(empty($email) || empty($pass)) {
            return $this->output_json(200 ,'debe enviar datos en los campos email y password',[],false);
        }
        $userDB = $this->UserModel->login($email, $pass);
        if (empty($userDB))
            {
                $this->data = [
                    'detalle' => "Email o password incorrectos"
                ];
                return $this->output_json(200 , 'No se encontro usuario',$this->data , false);
            } 
            
            $token_data['id'] = $userDB['ID_US'];
            $token_data['nombres'] = $userDB['NOMBRES'];
            $token_data['apellidos'] = $userDB['APELLIDO_PATERNO'];
            $token_data['email'] = $userDB['EMAIL'];
            $token_data['telefono'] = $userDB['TELEFONO'];
            $token_data['rol'] = $userDB['TIPO'];
            $token_data['time'] = time();

            $user_token = AUTHORIZATION::generateToken($token_data);

            $privileges = $this->PrivilegesModel->get($userDB['ID_PE']);
            $privileges = $this->converter_bool($privileges);

            $this->data = [
                'user_id' => $userDB['ID_US'],
                'nombres' => trim($userDB['NOMBRES']),
                'apellidos' => trim($userDB['APELLIDO_PATERNO']).' '.trim($userDB['APELLIDO_MATERNO']),
                'rol' => strtolower ($userDB['TIPO']),
                'permisos' => $privileges,
                'token' => $user_token,
            ];

            $this->output_json(200 , 'login success',$this->data);
    }
   
	
}
