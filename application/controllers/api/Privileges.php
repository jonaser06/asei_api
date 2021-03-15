<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Privileges extends MY_Controller {

	public function __construct()
    {
        parent::__construct();
		$this->load->model('Privileges_Model', 'PrivilegesModel');
        $this->load->model('User_Model', 'UserModel');
    }
	
	public function index() {
        
        $privileges = $this->PrivilegesModel->getAll();
        if( !$privileges ) return $this->output_json(200,'No se encontraron resultados',[] , false);
        return $this->output_json(200 , 'perfiles find !!' , $privileges);
           
	}
    public function get (int $id) : CI_Output
    {   
        $userDB = $this->UserModel->getOne(['ID_US' => (int)$id]);
        if( empty($userDB) ) return $this->output_json(200 , 'no se encontro user con el id', [],FALSE );

        return $this->output_json (200, "privilégios en cada módulo para el usuario con id $id ", $this->privileges_for_user( $id, $userDB['ID_PE']) );
    }
    public function privileges_for_user ( int $id , int $id_perfil )
    {
        
        $privileges_perfil = $this->converter_bool($this->PrivilegesModel->get($id_perfil)); #result array
        $user_privileges   = $this->PrivilegesModel->get_for_user($id); 
     
        if (!$user_privileges) return $privileges_perfil;
        $privileges_user   = $this->converter_bool($user_privileges);
        
        for ($i = 0; $i <count ($privileges_perfil ) ; $i++) { 
           $flag_find = false;
           for ($j = 0; $j <count($privileges_user) ; $j++ ) { 
            if($privileges_user[$j]['modulo'] == $privileges_perfil[$i]['modulo']):
            $flag_find =true;
            endif;
           }
           if(!$flag_find) array_push( $privileges_user, $privileges_perfil[$i]);   
        }
                  
        return $privileges_user;
    }
	
    public function edit(int $id) : CI_Output
    {
        $userDB = $this->UserModel->getOne( ['ID_US' => (int)$id] );
        if( empty($userDB) ) return $this->output_json(200 ,"no se encontro usuario con el id: $id ", [] ,FALSE );
        if( !$this->input->post('privileges')) return $this->output_json(400 ,'debe enviar el campo privileges con un formato json como valor');

        $_POST = $this->security->xss_clean($_POST);
        $privileges = json_decode($_POST['privileges'],TRUE);
        if( !$privileges ) return $this->output_json(400 , 'el valor contiene un JSON inválido');
        
        foreach ($privileges as $key => $value) {
            $module = $this->PrivilegesModel->get_module(['NOMBRE' => $key]);
            if( $module ) : 
                $set = [
                    'CREAR'      => isset($value['crear'])      ? $value['crear']      : TRUE,
                    'VISUALIZAR' => isset($value['visualizar']) ? $value['visualizar'] : TRUE,
                    'ACTUALIZAR' => isset($value['actualizar']) ? $value['actualizar'] : TRUE,
                    'ELIMINAR'   => isset($value['eliminar'])   ? $value['eliminar']   : TRUE,
                ];
                $where = ['ID_US'=> $id, 'ID_MO'=> $module['ID_MO']];
                $module_user = $this->PrivilegesModel->get_module_user($where);
                
                if( $module_user ) {
                    $this->PrivilegesModel->update_module($set , $where);
                }else {
                    $set['ID_US'] = $id;
                    $set['ID_MO'] = $module['ID_MO'];
                    $this->PrivilegesModel->insert_module($set);
                } 
            endif;
        }
        
         
        return $this->output_json(200 , "privilégios modificados para el usuario con id: $id");
    }
}
