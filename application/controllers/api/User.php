<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller {

    private $keysDB = ['nombres','apellido_paterno','apellido_materno','direccion','telefono','email','clave','cargo','fecha_ingreso','empresa','estado','id_notify'];
	public function __construct()
    {
	    	parent::__construct();
	      $this->load->model('User_Model', 'UserModel');
        $this->load->model('Perfil_Model', 'PerfilModel');
        $this->load->model('calificaciones_model', 'CalificacionesModel');

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
        
        $params     = $this->input->get(['page', 'limit', 'last','estado', 'search','empresa'], TRUE);
       
        $search   = ! $params['search'] ? [] : explode(' ', $params['search']) ;
        $for_page   = $params['limit'] ? (int) $params['limit'] : $users_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        $last       = $params['last'] == 'true' ? true :false;
        $conditions = ['p.ID_PE' => (int) $role['ID_PE']];
        
        if($params['estado']) {
            $estado   =  $params['estado'] == 'inactivo' ? 0 : 1 ;
            $conditions['usuarios.estado'] = $estado;
        }
        if($params['empresa']) {
            $conditions['usuarios.empresa'] = $params['empresa'];
        }

        $users = $this->UserModel->getAll( $for_page ,$offset ,$conditions , $last ,$search);
        if ( !$users )  return $this->output_json(200 , "no existen usuarios para este rol : ".$role['TIPO'] ,[] ,false );
        
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
      $user_imgs = $this->FileModel->getOne('ID_US','multimedia_usuarios',['ID_US' => $userDB['ID_US']]);
      if( !empty($user_imgs) ) $userDB['imagenes'] = $user_imgs;
      return $this->output_json(200 , 'usuario encontrado', $userDB);
    }
    
    public function delete( int $id )
    {   
        $note = $this->UserModel->get((int) $id);
        if( !$note ) return $this->output_json( 200 , 'no existe usuario con ese id' , [] , false );
        $user_imgs = $this->FileModel->getOne('ID_US','multimedia_usuarios',['ID_US' => $id]);
        $documentos = $this->FileModel->getOne('ID_US','usuarios_documentos',['ID_US' => $id],TRUE);
        $user_notas = $this->FileModel->get_entidadAll('usuarios_notas',['ID_US' => $id]);
        $this->FileModel->deleteallPersonalFiles($id);
        if($user_notas) :

            for ( $i = 0; $i < count( $user_notas ); $i++ ) { 
                $this->CalificacionesModel->delete((int)$user_notas[$i]['ID_NO'],(int) $id);
            }
        endif;
        if($documentos) :
            for ( $i = 0; $i < count( $documentos ); $i++ ) { 
                $this->deleteOneFile((int)$documentos[$i]['ID_DOC'],TRUE );  
            }
        endif;

        
        if($user_imgs) {
            for ( $i = 0; $i < count( $user_imgs ); $i++ ) { 
                $this->deleteFile('multimedia_usuarios',$user_imgs[$i]['ID_MULTI']);
            }
        }
       
        $resp = $this->UserModel->delete( (int) $id);

        return $resp ? $this->output_json( 200 , 'usuario eliminado!')
                     : $this->output_json( 500 , 'hubo un problema al eliminar el usuario!');
    }
    public function getAll() : CI_Output
    {
      $users_quanty = 9;
        $params     = $this->input->get(['page', 'limit', 'estado','last', 'search','empresa'], TRUE);
        $search   = ! $params['search'] ? [] : explode(' ', $params['search']) ;
        $conditions = [];
        if($params['estado']) {
            $estado   =  $params['estado'] == 'inactivo' ? 0 : 1 ;
            $conditions['usuarios.estado'] = $estado;
        }
        if($params['empresa']) {
            $conditions['usuarios.empresa'] = $params['empresa'];
        }        

        $for_page   = $params['limit'] ? (int) $params['limit'] : $users_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        $last       = $params['last'] == 'true' ? true :false;

        $users = $this->UserModel->getAll( $for_page ,$offset ,$conditions, $last ,$search);
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
      $set = $this->filter_attr( $_POST , $this->keysDB );
      $userUpdate = $this->UserModel->updateIdNotify($set,['ID_US'=>(int)$id ]);
      
      if ( !empty($_FILES['user_img']['name']) ) {
        $user_imgs = $this->FileModel->getOne('ID_US','multimedia_usuarios',['ID_US' => $id]);
        if (!$user_imgs) {
            $user_img['files'] = $_FILES['user_img'];
            $this->create_files('multimedia_usuarios','ID_US', (int)$userDB['ID_US'] , $user_img );
        }else {
            $img = $user_imgs[0];
            $user_img['files'] = $_FILES['user_img'];
            $this->editFile( $user_img ,$img['ID_MULTI']);
        }
      } 
      if( empty($userUpdate) ) return $this->output_json(200,'hubo un error al actualizar el usuario',[],false);
      return $this->output_json(200 , 'usuario actualizado' );

    }
    /**
     * @param post : data send for Client
     * @param keysDB : valid keys in DB
     * @return : valid data for insert
     */
    private function filter_attr ( array $post  , array $keysDB )
    {   
        $inputs = $this->security->xss_clean($post); #evitar un posible atac xcss
        $result = [];
        foreach ($inputs as $key => $value) {
            if (in_array($key , $keysDB )) {
                if ($key == 'clave') {
                    $result['clave'] = md5($value);
                }else {
                    $result[$key] = $value;
                }
            };
        }
        return $result;
    }
    
    
}
