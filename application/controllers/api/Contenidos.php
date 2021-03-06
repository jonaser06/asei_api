<?php

use phpDocumentor\Reflection\Types\String_;

defined('BASEPATH') OR exit('No direct script access allowed');

class Contenidos extends MY_Controller {

    protected $mpdf ;
    private $path = 'pdf/';

	public function __construct()
    {

        parent::__construct();
        date_default_timezone_set("America/Lima");        
        $this->load->model('certificados_model', 'CertificadosModel');
        $this->load->model('Contenido_Model', 'ContenidoModel');
        $this->load->model('User_Model', 'UserModel');

        $this->load->model('Files_Model','FileModel');

    }
    public function search( $categorie )
    {
        $notes_quanty = 3;
        $section = $this->NotesModel->get_section( [ 'nombre' => $categorie,'ID_MOD' => 3 ]);
        if ( !$section ) return $this->output_json(200 , 'Not exists this section' , [] , false );
        
        $params     = $this->input->get(['page', 'limit', 'search'], TRUE);
        $search   = ! $params['search'] ? [] : explode(' ', $params['search']) ;
        
        $for_page   = $params['limit'] ? (int) $params['limit'] : $notes_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        
        $conditions = ['notas.ID_SEC' => (int) $section['ID_SEC']];

        $notes = $this->NotesModel->getAll( $for_page ,$offset ,$conditions ,false ,$search );
        if ( !$notes )  return $this->output_json(200 , "not exists results" ,[] ,false );
    
        for( $i = 0; $i < count( $notes['notes'] ) ; $i ++ ): 

            $time = explode(' ',$notes['notes'][$i]['fecha_publicacion']);
            $notes['notes'][$i]['fecha_publicacion'] = $time[0];
            $notes['notes'][$i]['hora_publicacion']  = $time[1];

            $note_imgs = $this->FileModel->getOne('ID_NO','multimedia_notas',['ID_NO' => $notes['notes'][$i]['ID_NO']]);
            $notes['notes'][$i]['imagenes'] = $note_imgs ? $note_imgs : 'no images found';
            
        endfor;

        $page           = $params['page'] ? (int) $params['page'] : 1 ;
        $notes['page']  = $page;
        $pages          = ($notes['countAll'] % $for_page ) ?   (int)($notes['countAll'] / $for_page) + 1 : (int)$notes['countAll'] / $for_page  ; 
        $notes['pages'] = $pages;
        $section        = $notes['notes'][0]['seccion'];

        $busqueda  = $params['search'] ;
        if($page > 1) {
            $prev = $page - 1  ;
            $notes['prev'] = "/$section?page=$prev&limit=$for_page&search=$busqueda";
        } 
        if( $page < $pages ) {
            $next = $page + 1 ;
            $notes['next'] = "/$section?page=$next&limit=$for_page&search=$busqueda";
        }
       
        $this->output_json( 200 , 'find notes for this section !' , $notes );
    } 
    private function filterEmpty ( array $inputs = [] ) 
    {
        foreach( $inputs as  $input => $value ){
            $white_list = ['titulo' , 'resumen' , 'texto' , 'fecha_inicio' , 'fecha_fin', 'seccion'];
            
            if( $value =='' ) return $this->output_json(400 , 'El campo '.$input. ' no debe estar vacio'); 
        }
    }
    private function saveFormat(string $text = '' )  
    {   
        $parrafos = explode("\n",$text);
        $parrafosFormat = array_map(function($parrafo){
            return '<p class="italic-paragraph">'.$parrafo.'</p>';
        },$parrafos);
        return implode(' ',$parrafosFormat);
    }
    
    public function get_sections() {
        $sections = $this->NotesModel->get_section();
        return $sections ? $this->output_json( 200 , 'sections find !' ,$sections ) 
                         : $this->output_json( 200 , 'no exist any section !' ,[] ,false); 
    }
    private function sesiones_for_insert( array $nombres , array $links  , int $id) : array 
    {
        $sessionData = [];
       
        for($i = 0; $i < count($nombres); $i++){
                $sessionData[$i]['ID_SE']    = $this->generateId();
                $sessionData[$i]['nombre']   = $nombres[$i];
                $sessionData[$i]['link']     = $links[$i];
                $sessionData[$i]['ID_CO']    = $id; 
                $sessionData[$i]['FECHA_REGISTRO']    = date('Y-m-d H:i:s'); 

        }
        return $sessionData;
    } 
    private function capacitadores_for_insert( array $nombres , array $resumen  , int $id) : array 
    {
        $capacitadoresData = [];
       
        for($i = 0; $i < count($nombres); $i++){
                $capacitadoresData[$i]['ID_CA']    = $this->generateId();
                $capacitadoresData[$i]['nombre']   = $nombres[$i];
                $capacitadoresData[$i]['resumen']  = $resumen[$i];
                $capacitadoresData[$i]['ID_CO']    = $id; 
        }
        return $capacitadoresData;
    } 
    private function capacitador_send( array $capacitadores ) 
    {
        return array_map( function ( $capacitador ) {
            $img = $this->FileModel->getOne('ID_CA','multimedia_capacitadores',['ID_CA' => (int) $capacitador['ID_CA']]);
            $capacitador['foto'] = $img[0]['RUTA'];
            return $capacitador;
        },$capacitadores);
    }
    private function create_files_cap ($capacitadores , $cap_files){
        $cap = [];
            for($i = 0; $i < count($cap_files['files']['name']); $i++){
                $cap['files']['name'][0]     = $cap_files['files']['name'][$i];
                $cap['files']['type'][0]     = $cap_files['files']['type'][$i];
                $cap['files']['tmp_name'][0] = $cap_files['files']['tmp_name'][$i];
                $cap['files']['error'][0]    = $cap_files['files']['error'][$i];
                $cap['files']['size'][0]     = $cap_files['files']['size'][$i];
                
                $this->create_files('multimedia_capacitadores','ID_CA', (int)$capacitadores[$i]['ID_CA'] ,$cap );
            }
        
    }

   
    public function insert() :CI_Output
    {      
        
        if( ! $this->input->post('seccion') )        return $this->output_json(400 , 'Debe enviar la sección'); 
        if( ! $this->input->post('titulo') )         return $this->output_json(400 , 'Debe enviar el título');
        if( ! $this->input->post('resumen') )        return $this->output_json(400 , 'Debe enviar el resumen');
        if( ! $this->input->post('objetivo') )       return $this->output_json(400 , 'Debe enviar el Objetivo');
        if( ! $this->input->post('duracion') )       return $this->output_json(400 , 'Debe enviar la duracion');
        if ( empty($_FILES['img_learn']['name']) )   return $this->output_json(400 , 'Debe seleccion una imagen para el webinnar o curso');    
        
        if( ! $this->input->post('sesion_nombres') ) return $this->output_json(400 , 'Debe enviar el nombre por cada sesión');
        if( ! $this->input->post('sesion_links') )   return $this->output_json(400 , 'Debe enviar un link por sesión');
        
        if( !( count($this->input->post('sesion_nombres')) == count($this->input->post('sesion_links')) ) )return $this->output_json(400 , 'Debe enviar un link y un nombre por cada sesión');
        
        if( ! $this->input->post('cap_nombres') )    return $this->output_json(400 , 'Debe enviar un nombre por capacitador'); 
        if( ! $this->input->post('cap_resumen') )    return $this->output_json(400 , 'Debe enviar un resumen por capacitador');
        if ( empty($_FILES['cap_img']['name']) )       return $this->output_json(400 , 'Debe seleccionar una foto por capacitador');   
        if( ! (  count($this->input->post('cap_nombres')) == count($this->input->post('cap_resumen')) && count($this->input->post('cap_nombres')) == count($_FILES['cap_img']['name'])) ) return $this->output_json(400 , 'Debe enviar un nombre y un resumen e imagen por cada capacitador agregado'); 
        if ( $_FILES['cap_img']['size'][0] > 2000000 ) return $this->output_json(400 , 'La imagen debe ser menor a 2MB' );   

        $section = $this->ContenidoModel->get_section( [ 'nombre' => $this->input->post('seccion'),'ID_MOD' => 4 ]);
        if( !$section ) return $this->output_json(200 , 'No existe la seccion en ASEI LEARNING debe enviar webinnars o cursos' , [] , false );

        $inputs = $this->input->post(NULL, TRUE);
        $learn_files['files']         = $_FILES['img_learn'];
        $capacitadores_files['files'] = $_FILES['cap_img'];
        
        $content = [
            'ID_CO'           => $this->generateId(),
            'titulo'          => $inputs['titulo'],
            'resumen'         => $inputs['resumen'],
            'objetivo'        => $inputs['objetivo'],
            'duracion'        => $inputs['duracion'],
            'ID_SEC'          => (int)$section['ID_SEC'],
            'FECHA_PUBLISHED' => date("Y-m-d H:i:s")
        ];
       
        
        $sesiones      = $this->sesiones_for_insert( $inputs['sesion_nombres'],$inputs['sesion_links'], $content['ID_CO'] );
        $capacitadores = $this->capacitadores_for_insert( $inputs['cap_nombres'],$inputs['cap_resumen'], $content['ID_CO'] );

        $learn = $this->ContenidoModel->insert( $content);
        if( !$learn )   return $this->output_json(400 , 'Fallo la insercción');
        $this->create_files('multimedia_contenido','ID_CO', (int)$content['ID_CO'] ,$learn_files );

        $sesionesDB = $this->ContenidoModel->insert_rows($sesiones, 'sesiones');
        if( !$sesionesDB) return $this->output_json(400 , 'Fallo en insertar las sesiones.');
        $capacitadoresDB = $this->ContenidoModel->insert_rows($capacitadores, 'capacitadores');
        if( !$capacitadoresDB) return $this->output_json(400 , 'Fallo en insertar los capacitadores.');
        $this->create_files_cap ( $capacitadores , $capacitadores_files );
          
        $learn       = $this->ContenidoModel->get( (int)$content['ID_CO']);
        $learn_imgs  = $this->FileModel->getOne('ID_CO','multimedia_contenido',['ID_CO' => (int) $learn['ID_CO']]);

        if( !empty($learn) ) $learn['files'] = $learn_imgs;

        $sesionesDB  = $this->ContenidoModel->get_sesiones( (int)$learn['ID_CO']);
        $capsDB       = $this->ContenidoModel->get_capacitadores( (int)$learn['ID_CO']);
        $learn['capacitadores'] = $this->capacitador_send($capsDB);
        $learn['sesiones']      = $sesionesDB;
        
        #notification
        if($this->input->post('notificacion')):
            $notification = json_decode($this->input->post('notificacion'), TRUE);
            $this->newNotification($notification['message'], $notification['type'],$notification['idus'],$learn['ID_CO']);
           endif;
       

        return $this->output_json(200 , 'learn insert', $learn);
    }
    public function getById( string $tipo , int $id ): CI_Output
    {
        $section = $this->ContenidoModel->get_section( [ 'nombre' => $tipo,'ID_MOD' => 4 ]);
        if( !$section ) return $this->output_json(200 , 'No existe la seccion en ASEI LEARNING' , [] , false );
        $learn = $this->ContenidoModel->get((int) $id , ['contenido.ID_SEC' => $section['ID_SEC']]);

        if(!$learn) return $this->output_json( 200 , "El id es incorrecto , no existe este conetenido en $tipo" , [] , false );
        $learn['notificacion'] = json_decode($learn['notificacion'],true);
        $learn_imgs  = $this->FileModel->getOne('ID_CO','multimedia_contenido',['ID_CO' => (int) $learn['ID_CO']]);
        if( !empty($learn) ) $learn['files'] = $learn_imgs;
        $sesionesDB  = $this->ContenidoModel->get_sesiones( (int)$learn['ID_CO']);
        $capsDB      = $this->ContenidoModel->get_capacitadores( (int)$learn['ID_CO']);
        $learn['capacitadores'] = $this->capacitador_send($capsDB);
        $learn['sesiones']      = $sesionesDB;
        return $this->output_json(200 , "$tipo encontrado !",$learn);
    }
    public function get( $categorie ):CI_Output
    {
        $notes_quanty = 3;

        $section = $this->ContenidoModel->get_section( [ 'nombre' => $categorie ,'ID_MOD' => 4]);
        if ( !$section ) return $this->output_json(200 , 'No existe la sección en LEARNING CENTER' , [] , false );
        
        $params     = $this->input->get(['page', 'limit', 'last', 'search'], TRUE);
        $search   = ! $params['search'] ? [] : explode(' ', $params['search']) ;
        $for_page   = $params['limit'] ? (int) $params['limit'] : $notes_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        $last       = $params['last'] == 'true' ? true :false;
        $conditions = ['contenido.ID_SEC' => (int) $section['ID_SEC']];

        $contenido = $this->ContenidoModel->getAll( $for_page ,$offset ,$conditions , false , $search );
        
        if ( !$contenido )  return $this->output_json(200 , "not no se encontraron resultados en  : $categorie" ,[] ,false );
        
        for( $i = 0; $i < count( $contenido['contenido'] ) ; $i ++ ): 

            $note_imgs = $this->FileModel->getOne('ID_CO','multimedia_contenido',['ID_CO' => $contenido['contenido'][$i]['ID_CO']]);
            $contenido['contenido'][$i]['imagenes'] = $note_imgs ? $note_imgs : 'no images found';
            $sesionesDB  = $this->ContenidoModel->get_sesiones( (int)$contenido['contenido'][$i]['ID_CO']);
            $capsDB       = $this->ContenidoModel->get_capacitadores( (int)$contenido['contenido'][$i]['ID_CO']);
            #caps 
            if( $capsDB) {
                $contenido['contenido'][$i]['capacitadores'] = $this->capacitador_send($capsDB);
            }
            if ($sesionesDB ) {
                $contenido['contenido'][$i]['sesiones']      = $sesionesDB;
            }
        endfor;

        $page           = $params['page'] ? (int) $params['page'] : 1 ;
        $contenido['page']  = $page;
        $pages          = ($contenido['countAll'] % $for_page ) ?   (int)($contenido['countAll'] / $for_page) + 1 : (int)$contenido['countAll'] / $for_page  ; 
        $contenido['pages'] = $pages;
        $section        = $contenido['contenido'][0]['tipo'];

        if($page > 1) {
            $prev = $page - 1  ;
            $contenido['prev'] = "/$section?page=$prev&limit=$for_page";
        } 
        if( $page < $pages ) {
            $next = $page + 1 ;
            $contenido['next'] = "/$section?page=$next&limit=$for_page";
        }
       
        return $this->output_json( 200 , "Se encontro contenido en  $categorie!" , $contenido );
    } 
    public function update (string $seccion,int $id):CI_Output
    {
        $section = $this->ContenidoModel->get_section( [ 'nombre' => $seccion ,'ID_MOD' => 4 ]);
        if( !$section ) return $this->output_json(200 , 'No existe la seccion en ASEI LEARNING debe enviar webinnar o curso' , [] , false );
        $learn = $this->ContenidoModel->get((int) $id , ['contenido.ID_SEC' => $section['ID_SEC']] );
        if( !$learn ) return $this->output_json( 200 , "El id es incorrecto , no existe este conetenido en $seccion " , [] , false );

        if( ! $this->input->post('titulo') )         return $this->output_json(400 , 'Debe enviar el título');
        if( ! $this->input->post('resumen') )        return $this->output_json(400 , 'Debe enviar el resumen');
        if( ! $this->input->post('objetivo') )       return $this->output_json(400 , 'Debe enviar el Objetivo');
        if( ! $this->input->post('duracion') )       return $this->output_json(400 , 'Debe enviar la duracion');
       
         $inputs = $this->input->post(NULL, TRUE);
         
         $set = [
             'titulo'          => $inputs['titulo'],
             'resumen'         => $inputs['resumen'],
             'objetivo'        => $inputs['objetivo'],
             'duracion'        => $inputs['duracion'],
             'ID_SEC'          => (int)$section['ID_SEC'],
         ];

        #notification
        if($this->input->post('notificacion')):
            $notification = json_decode($this->input->post('notificacion'), TRUE);
            $this->newNotification($notification['message'], $notification['type'],$notification['idus'],$id);
        endif;

        if ( !empty($_FILES['img_learn']['name']) ) {
            $contenido_imgs = $this->FileModel->getOne('ID_CO','multimedia_contenido',['ID_CO' => $id]);
            if (!$contenido_imgs) {
                $contenido_imgs['files'] = $_FILES['img_learn'];
                $this->create_files('multimedia_contenido','ID_CO', (int)$id , $contenido_imgs );
            }else {
                $img = $contenido_imgs[0];
                $contenido_imgs['files'] = $_FILES['img_learn'];
                $this->editFile( $contenido_imgs ,$img['ID_MULTI']);
            }
          } 
        $contenidoUpdate = $this->ContenidoModel->update( $set , ['ID_CO' => $id] );
        if( !$contenidoUpdate ) return $this->output_json( 400 , 'Error not update learn!');
         return $this->output_json(200 , 'update contenido');

    }
   
    public function delete( string $tipo ,int $id ):CI_Output
    {
        $section = $this->ContenidoModel->get_section( [ 'nombre' => $tipo,'ID_MOD' => 4 ]);
        if( !$section ) return $this->output_json(200 , 'No existe la seccion en ASEI LEARNING' , [] , false );

        $contenido = $this->ContenidoModel->get((int) $id , ['contenido.ID_SEC' => $section['ID_SEC']]);
        if( !$contenido ) return $this->output_json( 200 , "id is incorrect , no existe este contenido en $tipo " , [] , false );

        #contenido
        $contenido_imgs = $this->FileModel->getOne('ID_CO','multimedia_contenido',[ 'ID_CO' => (int)$id]);
        if($contenido_imgs) {
            for ( $i = 0; $i < count( $contenido_imgs ); $i++ ) { 
                $this->deleteFile('multimedia_contenido',$contenido_imgs[$i]['ID_MULTI']);
            }
        }
        #sesiones 

        $capacitadoresDB   = $this->ContenidoModel->get_capacitadores( (int)$contenido['ID_CO']);
        
        $sesionesDB  = $this->ContenidoModel->get_sesiones( (int)$contenido['ID_CO']);

        if($capacitadoresDB) {
            $caps_imgs = [];
            for ( $i = 0; $i < count( $capacitadoresDB ); $i++ ) { 
                $capacitador_imgs =  $this->FileModel->getOne('ID_CA','multimedia_capacitadores',['ID_CA' => (int) $capacitadoresDB[$i]['ID_CA']]);
                if($capacitador_imgs) {
                    array_push($caps_imgs , $capacitador_imgs);
                }
                
            }
            for ( $i = 0; $i < count( $caps_imgs ); $i++ ) { 
                $this->deleteFile('multimedia_capacitadores',$caps_imgs[$i][0]['ID_MULTI']);
            }
            
        }
        
        $this->ContenidoModel->remove( 'sesiones' , ['ID_CO' => (int)$contenido['ID_CO']]);
        $this->ContenidoModel->remove( 'capacitadores' , ['ID_CO' => (int)$contenido['ID_CO']]);
        $resp = $this->ContenidoModel->delete( (int) $id);
        
        return $resp ? $this->output_json( 200 , 'delete contenido!')
                     : $this->output_json( 500 , 'have a problem with contenido deleted!');
    }

    public function test( ):CI_Output
    {
        $data = [
            'id'           => $this->generateId(),
            'code'          => $this->generateId().'code',
            'state'         => 1
        ];
        $dataDB = $this->db->insert('test',$data);
        return $this->output_json(200 , 'insert db ', $data );
    }

    public function addCap( string $seccion ,int $id_learn) 
    {
        
        $section = $this->ContenidoModel->get_section( [ 'nombre' => $seccion ,'ID_MOD' => 4 ]);
        if( !$section ) return $this->output_json(200 , 'No existe la seccion en ASEI LEARNING debe enviar webinnar o curso' , [] , false );
        $learn = $this->ContenidoModel->get((int) $id_learn , ['contenido.ID_SEC' => $section['ID_SEC']] );

        if( !$learn ) return $this->output_json( 200 , "El id es incorrecto , no existe este conetenido en $seccion " , [] , false );
        if( ! $this->input->post('cap_nombres') )    return $this->output_json(400 , 'Debe enviar un nombre por capacitador'); 
        if( ! $this->input->post('cap_resumen') )    return $this->output_json(400 , 'Debe enviar un resumen por capacitador');
        if ( empty($_FILES['cap_img']['name']) )     return $this->output_json(400 , 'Debe seleccionar una foto por capacitador');  
        $inputs = $this->input->post(NULL, TRUE);

        $capacitadores_files['files'] = $_FILES['cap_img'];
        
        $capacitadores = $this->capacitadores_for_insert( $inputs['cap_nombres'],$inputs['cap_resumen'], (int)$id_learn);
        $capacitadoresDB = $this->ContenidoModel->insert_rows($capacitadores, 'capacitadores');
        if( !$capacitadoresDB) return $this->output_json(400 , 'Fallo en insertar los capacitadores.');
        $this->create_files_cap ( $capacitadores , $capacitadores_files );
        return $this->output_json(200 , 'capacitador insertado.');


    }
    public function removeCap( int $id_cap ) 
    {
        $capacitador_imgs =  $this->FileModel->getOne('ID_CA','multimedia_capacitadores',['ID_CA' => (int) $id_cap]);
        if($capacitador_imgs) {
            for ( $i = 0; $i < count( $capacitador_imgs ); $i++ ) { 
                $this->deleteFile('multimedia_capacitadores',$capacitador_imgs[$i]['ID_MULTI']);
            }
        }
        $resp = $this->ContenidoModel->remove( 'capacitadores' , ['ID_CA' => (int)$id_cap]);
        return $resp ? $this->output_json( 200 , 'delete capacitador!')
        : $this->output_json( 500 , 'have a problem with capacitador deleted!');
    }
    public function addSession(string $seccion , int $id_learn) 
    {
        $section = $this->ContenidoModel->get_section( [ 'nombre' => $seccion ,'ID_MOD' => 4 ]);
        if( !$section ) return $this->output_json(200 , 'No existe la seccion en ASEI LEARNING debe enviar webinnar o curso' , [] , false );
        $learn = $this->ContenidoModel->get((int) $id_learn , ['contenido.ID_SEC' => $section['ID_SEC']] );
        if( !$learn ) return $this->output_json( 200 , "El id es incorrecto , no existe este conetenido en $seccion " , [] , false );

        if( ! $this->input->post('sesion_nombres') ) return $this->output_json(400 , 'Debe enviar el nombre por cada sesión');
        if( ! $this->input->post('sesion_links') )   return $this->output_json(400 , 'Debe enviar un link por sesión');
        $inputs = $this->input->post(NULL, TRUE);

        $sesiones      = $this->sesiones_for_insert( $inputs['sesion_nombres'],$inputs['sesion_links'], $id_learn );
        $sesionesDB = $this->ContenidoModel->insert_rows($sesiones, 'sesiones');
        if( !$sesionesDB) return $this->output_json(400 , 'Fallo en insertar la session.');
        return $this->output_json(201 , 'session insert');
    }
    public function removeSession(int $id_session ) 
    {
        $resp = $this->ContenidoModel->remove( 'sesiones' , ['ID_SE' => (int)$id_session]);
        return $resp ? $this->output_json( 200 , 'delete sesión !')
        : $this->output_json( 500 , 'have a problem with capacitador deleted!');
    }


    

    public function getByIdCer(int $id): CI_Output
    {
       

        // $userDB = $this->UserModel->get($id_us);
        // if( empty($userDB) ) return $this->output_json(200 , 'no se encontro user con el id' , [] , false );

    
        $certificadosDB = $this->CertificadosModel->get_certificado_us($id);

        if(!$certificadosDB) return $this->output_json(200 , 'No existe este certificado',[],FALSE);
        return $this->output_json(200 , "certificado encontrado !",$certificadosDB);
    }
    public function createcertificate(int $id , $show = true)
    {
    

        
        $certificadosDB = $this->CertificadosModel->get_certificado_us($id);
        
        if(!$certificadosDB) return $this->output_json(200 , 'No existe este certificado',[],FALSE);
         $this->mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir().DIRECTORY_SEPARATOR.'mpdf','mode' => 'utf-8', 'format' => 'A4-L']);
            $date = $certificadosDB['fecha_emited'];
            $name = "cert-$date".'.pdf';
            
            $this->mpdf->SetDefaultBodyCSS('background', "url('https://i.pinimg.com/originals/c3/f2/fe/c3f2feb9bc21922656b0c7ff8c09c8ac.png')");
            $this->mpdf->SetDefaultBodyCSS('background-image-resize', 6);
            $html =$this->load->view( $this->path.'certificate', $certificadosDB , TRUE);
            $this->mpdf->WriteHTML($html);
            $show ? $this->mpdf->Output($name,"I") : $this->mpdf->Output($name,"D"); 
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

    
        $certificadosDB = $this->CertificadosModel->comproved_certificate($id_co, $id_us);

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
            'capacitador'    => $this->input->post('capacitador'),
            'fecha_emited'   => date('Y-m-d'),
            
        ];

        $result = $this->CertificadosModel->insert($data);
        if( !$result ) return $this->output_json( 400 , 'No se puede guardar el registro para el certificado intentelo mas tarde');
        return $this->output_json( 200 , 'se guardo con éxito el registro' );

    }   



}
