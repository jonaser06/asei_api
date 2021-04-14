<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificados extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set("America/Lima");        

		    $this->load->model('certificados_model', 'CertificadosModel');
            $this->load->model('User_Model', 'UserModel');
            $this->load->model('Contenido_Model', 'ContenidoModel');


    } 

    private function decorador_prom(array $registros ): array {
        
        $registro_depurado = array_map(function($registro){

            $registro["PROMEDIO_ESTRELLAS"]=(double)number_format((float)$registro["PROMEDIO_ESTRELLAS"],1);

            return $registro ; 

        },$registros);

        return $registro_depurado ;

        
    }

    private function decorador_suma(array $registros ): array {
        
        $registro_depurado = array_map(function($registro){

            $registro["SUMA_ESTRELLAS"]=(double)number_format((float)$registro["SUMA_ESTRELLAS"],1);

            return $registro ; 

        },$registros);

        return $registro_depurado ;

        
    }

    
   

    public function comproved ( int $id_co , int $id_us) 
    {
        $userDB = $this->UserModel->get($id_us);
        if( empty($userDB) ) return $this->output_json(200 , 'no se encontro user con el id' , [] , false );

        $note = $this->NotesModel->get((int) $id_co);

        if(!$note) return $this->output_json( 200 , 'id is incorrect , not exist note ' , [] , false );

        $calificationDB = $this->CalificacionesModel->get_calification_us($id_co, $id_us);

        if( $calificationDB ) return $this->output_json(200 , 'la nota ya fue calificada por este usuario' , [] , false);
        return $this->output_json(200 , 'califique esta nota ');

    }





    public function getById(int $id_co , $id_us): CI_Output
    {
       

        $userDB = $this->UserModel->get($id_us);
        if( empty($userDB) ) return $this->output_json(200 , 'no se encontro user con el id' , [] , false );

    
        $certificadosDB = $this->CertificadosModel->get_certificado_us($id_co, $id_us);

        if(!$certificadosDB) return $this->output_json(200 , 'No existe este certificado',[],FALSE);
        return $this->output_json(200 , "certificado encontrado !",$certificadosDB);
    }
    public function get_certificates( $id )
    {
        $userDB = $this->UserModel->get($id);
        if( empty($userDB) ) return $this->output_json(200 , 'no se encontro user con el id' , [] , false );
        $notes_quanty = 6;
        $certificates = $this->CertificadosModel->get_certificados_us( $id );
        if ( !$certificates ) return $this->output_json(200 , 'El usuario aun no tiene certificados' , [] , false );
        
        $params     = $this->input->get(['page', 'limit', 'last', 'search'], TRUE);
        $search   = ! $params['search'] ? [] : explode(' ', $params['search']) ;
        $for_page   = $params['limit'] ? (int) $params['limit'] : $notes_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        $last       = $params['last'] == 'true' ? true :false;
        $conditions = ['ID_US' => (int)$id];

        $contenido = $this->CertificadosModel->getAll( $for_page ,$offset ,$conditions , $last , $search );
        if ( !$contenido )  return $this->output_json(200 , "no se encontraron resultados en  : $id" ,[] ,false );
        $page           = $params['page'] ? (int) $params['page'] : 1 ;
        $contenido['page']  = $page;
        $pages          = ($contenido['countAll'] % $for_page ) ?   (int)($contenido['countAll'] / $for_page) + 1 : (int)$contenido['countAll'] / $for_page  ; 
        $contenido['pages'] = $pages;

        if($page > 1) {
            $prev = $page - 1  ;
            $contenido['prev'] = "/$id/files?page=$prev&limit=$for_page";
        } 
        if( $page < $pages ) {
            $next = $page + 1 ;
            $contenido['next'] = "/$id/files?page=$next&limit=$for_page";
        }
       
        $this->output_json( 200 , "Se encontraron certificados para este usuario" , $contenido );
    }


    public function set_certificate(int $id_co , int $id_us )
    {

        $learn = $this->ContenidoModel->get((int) $id_co);
        if(!$learn) return $this->output_json( 200 , "El id es incorrecto , no existe este conetenido el curso ");

        $userDB = $this->UserModel->get($id_us);
        if( empty($userDB) ) return $this->output_json(200 , 'no se encontro user con el id' , [] , false );

    
        $certificadosDB = $this->CertificadosModel->get_certificado_us($id_co, $id_us);

        if( $certificadosDB ) return $this->output_json(200 , 'la ya se emitio certificado de este curso para este usuario'[],FALSE);

        if ( !$this->input->post('user') ) return $this->output_json( 400 , 'Debe enviar el usuario a emitir certificado'); 
        if ( !$this->input->post('curse_name') ) return $this->output_json( 400 , 'Debe enviar debe enviar el nombre del curso'); 
        if ( !$this->input->post('curse_inicio') ) return $this->output_json( 400 , 'Debe enviar la fecha de inicio del curso'); 
        if ( !$this->input->post('curse_duration') ) return $this->output_json( 400 , 'Debe enviar la duración del curso'); 
        
        $data = [
            'ID_US'          => $id_us,
            'ID_CO'          => $id_co,
            'user'           => $this->input->post('user'),
            'curse_name'     =>  $this->input->post('curse_name'),
            'curse_inicio'   => $this->input->post('curse_inicio'),
            'curse_duration' => $this->input->post('curse_duration'),
            'fecha_emited'   => date('Y-m-d'),
        ];

        $result = $this->CertificadosModel->insert($data);
        if( !$result ) return $this->output_json( 400 , 'No se puede guardar el registro para el certificado intentelo mas tarde');
        return $this->output_json( 200 , 'se guardo con éxito el registro' );

    }   
}