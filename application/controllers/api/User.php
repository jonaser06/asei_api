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
	
	
    public function getRol( $role ):CI_Output
    {
        $users_quanty = 9;

        $role = $this->UserModel->get_profile( ['TIPO' => $role ]);
        if ( !$role ) return $this->output_json(200 , 'Not exists this role' , [] , false );
        
        $params     = $this->input->get(['page', 'limit', 'last', 'search'], TRUE);
        $for_page   = $params['limit'] ? (int) $params['limit'] : $users_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        $last       = $params['last'] == 'true' ? true :false;
        $conditions = ['perfiles.ID_PE' => (int) $role['ID_PE']];

        $users = $this->UserModel->getAll( $for_page ,$offset ,$conditions , $last );
        if ( !$users )  return $this->output_json(200 , "no existen usuarios para este rol : $role" ,[] ,false );
        
        for( $i = 0; $i < count( $users['users'] ) ; $i ++ ): 
            $user_imgs = $this->FileModel->getOne('ID_US','multimedia_usuarios',['ID_US' => $users['users'][$i]['ID_US']]);
            $users['users'][$i]['imagenes'] = $user_imgs ? $user_imgs : 'no images found';

        endfor;

        $page           = $params['page'] ? (int) $params['page'] : 1 ;
        $users['page']  = $page;
        $pages          = ($users['countAll'] % $for_page ) ?   (int)($users['countAll'] / $for_page) + 1 : (int)$users['countAll'] / $for_page  ; 
        $users['pages'] = $pages;
        $perfil        = $role['TIPO'];

        if($page > 1) {
            $prev = $page - 1  ;
            $users['prev'] = "$perfil/?page=$prev&limit=$for_page";
        } 
        if( $page < $pages ) {
            $next = $page + 1 ;
            $users['next'] = "$perfil/?page=$next&limit=$for_page";
        }
       
        return $this->output_json( 200 , 'usuarios encontrados !' , $users );
    } 
    public function get($id) : CI_Output
    {
      if (!(int)$id)    return $this->output_json(400 ,'param id is required'); 
      $userDB = $this->UserModel->get($id);
      if( empty($userDB) ) return $this->output_json(200 , 'no se encontro user con el id' );
      return $this->output_json(200 , 'usuario encontrado', $userDB);
    }
    
    public function remove()
    {
     
    }
    public function getAll() : CI_Output
    {
      $users_quanty = 9;

        
        $params     = $this->input->get(['page', 'limit', 'last', 'search'], TRUE);
        $for_page   = $params['limit'] ? (int) $params['limit'] : $users_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        $last       = $params['last'] == 'true' ? true :false;

        $users = $this->UserModel->getAll( $for_page ,$offset ,[] , $last );
        if ( empty($users) )  return $this->output_json(200 , "no existen usuarios" ,[] ,false );
        
        for( $i = 0; $i < count( $users['users'] ) ; $i ++ ): 
            $user_imgs = $this->FileModel->getOne('ID_US','multimedia_usuarios',['ID_US' => $users['users'][$i]['ID_US']]);
            $users['users'][$i]['imagenes'] = $user_imgs ? $user_imgs : 'no images found';

        endfor;

        $page           = $params['page'] ? (int) $params['page'] : 1 ;
        $users['page']  = $page;
        $pages          = ($users['countAll'] % $for_page ) ?   (int)($users['countAll'] / $for_page) + 1 : (int)$users['countAll'] / $for_page  ; 
        $users['pages'] = $pages;

        if($page > 1) {
            $prev = $page - 1  ;
            $users['prev'] = "/users?page=$prev&limit=$for_page";
        } 
        if( $page < $pages ) {
            $next = $page + 1 ;
            $users['next'] = "/users?page=$next&limit=$for_page";
        }
       
        return $this->output_json( 200 , 'all user find !' , $users );
     
    }
    public function updateOne($id): CI_Output
    {
      $userDB = $this->UserModel->get($id);
      if( empty($userDB) ) return $this->output_json(200 , 'no se encontro user con el id' ,[] , false );
      $id_notify = $this->input->post('id_notify',TRUE);
      $set = [
        'id_notify' => $id_notify
      ];
      
      $userUpdate = $this->UserModel->updateIdNotify($set,['ID_US'=>(int)$id ]);
      if( empty($userUpdate) ) return $this->output_json(200,'hubo un error al actualizar el usuario',[],false);
      return $this->output_json(200 , 'usuario actualizado' );

    }
}
