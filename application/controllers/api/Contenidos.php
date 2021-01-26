<?php

use phpDocumentor\Reflection\Types\String_;

defined('BASEPATH') OR exit('No direct script access allowed');

class Contenidos extends MY_Controller {

	public function __construct()
    {
        parent::__construct();
        date_default_timezone_set("America/Lima");        
        $this->load->model('Contenido_Model', 'ContenidoModel');
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
    private function noteSend(array $note = []):array
    {
        if($note['seccion'] == 'noticias') {
            unset($note['fecha_inicio']);
            unset($note['fecha_fin']);
            unset($note['hora_inicio']);
            unset($note['hora_fin']);
        }
        $time = explode(' ',$note['fecha_publicacion']);
        $note['fecha_publicacion'] = $time[0];
        $note['hora_publicacion']  = $time[1];
        return $note;
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
    public function insert()
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
        if ( empty($_FILES['files']['name']) )       return $this->output_json(400 , 'Debe seleccionar una foto por capacitador');   
        if( ! (  count($this->input->post('cap_nombres')) == count($this->input->post('cap_resumen')) && count($this->input->post('cap_nombres')) == count($_FILES['files']['name'])) ) return $this->output_json(400 , 'Debe enviar un nombre y un resumen e imagen por cada capacitador agregado'); 
        if ( $_FILES['files']['size'][0] > 2000000 ) return $this->output_json(400 , 'La imagen debe ser menor a 2MB' );   

        $section = $this->ContenidoModel->get_section( [ 'nombre' => $this->input->post('seccion'),'ID_MOD' => 4 ]);
        if( !$section ) return $this->output_json(200 , 'No existe la seccion en ASEI LEARNING debe enviar webinnar o curso' , [] , false );

        $inputs = $this->input->post(NULL, TRUE);
        $learn_files['files']         = $_FILES['img_learn'];
        $capacitadores_files['files'] = $_FILES['files'];
        
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
        
        return $this->output_json(200 , 'learn insert', $learn);
    }
  
    public function getById( string $tipo , int $id )
    {
        $section = $this->ContenidoModel->get_section( [ 'nombre' => $tipo,'ID_MOD' => 4 ]);
        if( !$section ) return $this->output_json(200 , 'No existe la seccion en ASEI LEARNING' , [] , false );

        $learn = $this->ContenidoModel->get((int) $id , ['contenido.ID_SEC' => $section['ID_SEC']]);
        
        if(!$learn) return $this->output_json( 200 , "El id es incorrecto , no existe este conetenido en $tipo" , [] , false );
        $learn_imgs  = $this->FileModel->getOne('ID_CO','multimedia_contenido',['ID_CO' => (int) $learn['ID_CO']]);
        if( !empty($learn) ) $learn['files'] = $learn_imgs;
        $sesionesDB  = $this->ContenidoModel->get_sesiones( (int)$learn['ID_CO']);
        $capsDB       = $this->ContenidoModel->get_capacitadores( (int)$learn['ID_CO']);
        $learn['capacitadores'] = $this->capacitador_send($capsDB);
        $learn['sesiones']      = $sesionesDB;
        return $this->output_json(200 , "$tipo encontrado !",$learn);
    }

    public function get( $categorie )
    {
        $notes_quanty = 3;

        $section = $this->ContenidoModel->get_section( [ 'nombre' => $categorie ,'ID_MOD' => 4]);
        if ( !$section ) return $this->output_json(200 , 'No existe la sección en LEARNING CENTER' , [] , false );
        
        $params     = $this->input->get(['page', 'limit', 'last', 'search'], TRUE);
        $for_page   = $params['limit'] ? (int) $params['limit'] : $notes_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        $last       = $params['last'] == 'true' ? true :false;
        $conditions = ['contenido.ID_SEC' => (int) $section['ID_SEC']];

        $contenido = $this->ContenidoModel->getAll( $for_page ,$offset ,$conditions , $last );
        if ( !$contenido )  return $this->output_json(200 , "not no se encontraron resultados en  : $categorie" ,[] ,false );
        
        for( $i = 0; $i < count( $contenido['contenido'] ) ; $i ++ ): 

            $note_imgs = $this->FileModel->getOne('ID_CO','multimedia_contenido',['ID_CO' => $contenido['contenido'][$i]['ID_CO']]);
            $contenido['contenido'][$i]['imagenes'] = $note_imgs ? $note_imgs : 'no images found';
            $sesionesDB  = $this->ContenidoModel->get_sesiones( (int)$contenido['contenido'][$i]['ID_CO']);
            $capsDB       = $this->ContenidoModel->get_capacitadores( (int)$contenido['contenido'][$i]['ID_CO']);
            $contenido['contenido'][$i]['capacitadores'] = $this->capacitador_send($capsDB);
            $contenido['contenido'][$i]['sesiones']      = $sesionesDB;
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
       
        $this->output_json( 200 , "Se encontro contenido en  $categorie!" , $contenido );
    } 
    public function update (int $id)
    {
        $note = $this->NotesModel->get((int) $id);
        if(!$note) return $this->output_json( 200 , 'id is incorrect , not exist note ' , [] , false );

        if( ! $this->input->post('titulo') )        return $this->output_json(400 , 'Debe enviar el título');
        if( ! $this->input->post('resumen') )       return $this->output_json(400 , 'Debe enviar el resumen');
        if( ! $this->input->post('texto') )         return $this->output_json(400 , 'Debe enviar el texto');
        if( ! $this->input->post('seccion') )       return $this->output_json(400 , 'Debe enviar la seccion');
        if($this->input->post('seccion') !== 'noticias') {
               if( ! $this->input->post('fecha_inicio') )  return $this->output_json(400 , 'Debe enviar la fecha de inicio');
               if( ! $this->input->post('fecha_fin') )     return $this->output_json(400 , 'Debe enviar la fecha de final de la nota');
               if( ! $this->input->post('hora_inicio') )   return $this->output_json(400 , 'Debe enviar la hora de inicio');
               if( ! $this->input->post('hora_fin') )      return $this->output_json(400 , 'Debe enviar la hora de finalización');
        }
        if( ! $this->input->post('fecha_publicacion') )  return $this->output_json(400 , 'Debe enviar la fecha publicacion');
        if( ! $this->input->post('hora_publicacion') )  return $this->output_json(400 , 'Debe enviar la hora de publicación');
        if( ! $this->input->post('link') )  return $this->output_json(400 , 'Debe enviar el link de la nota');

        if ( empty($_FILES['file']['name']) )      return $this->output_json(400 , 'no select any file');    
        if ( $_FILES['file']['size'] > 2000000 ) return $this->output_json(400 , 'La imagen debe ser menor a 2MB' );    


        $inputs = $this->input->post(NULL, TRUE);
        $section = $this->NotesModel->get_section( [ 'nombre' => $this->input->post('seccion') , 'ID_MOD' => 3 ]);
        if( !$section ) return $this->output_json(200 , 'Not exists this section for note' , [] , false );

        $text  = $this->saveFormat($inputs['texto']);
        $set = [
            'titulo'       => $inputs['titulo'],
            'resumen'      => $inputs['resumen'],
            'texto'        => $text,
            'link'         => $inputs['link'],
            'ID_SEC'       => (int)$section['ID_SEC'],
        ];

           if( $section['nombre'] == 'noticias') {
               $set['fecha_inicio']    = $inputs['fecha_publicacion'];
               $set['fecha_fin']       = $inputs['fecha_publicacion'];
               $set['hora_inicio']     = '00:00';
               $set['hora_fin']        = '00:00';
               $set['FECHA_PUBLISHED'] = $inputs['fecha_publicacion'].' '.$inputs['hora_publicacion'];
           }else {
               $set['fecha_inicio']    = $inputs['fecha_inicio'];
               $set['fecha_fin']       = $inputs['fecha_fin'];
               $set['hora_inicio']     = $inputs['hora_inicio'];
               $set['hora_fin']        = $inputs['hora_fin'];
               $set['FECHA_PUBLISHED'] = $inputs['fecha_publicacion'].' '.$inputs['hora_publicacion'];
           }
       

        $noteUpdate = $this->NotesModel->update( $set , ['ID_NO' => $id] );
        if( !$noteUpdate ) return $this->output_json( 400 , 'Error not update note!');
        $note_imgs = $this->FileModel->getOne('ID_NO','multimedia_notas',['ID_NO' => $id]);
        $img = $note_imgs[0];
        $this->editFile( $_FILES ,$img['ID_MULTI']);
        return $this->output_json( 200 , 'note update !');


    }
    public function delete( int $id )
    {
        $note = $this->NotesModel->get((int) $id);
        if( !$note ) return $this->output_json( 200 , 'id is incorrect , not exist note ' , [] , false );

        $note_imgs = $this->FileModel->getOne('ID_NO','multimedia_notas',[ 'ID_NO' => $id]);
        if($note_imgs) {
            for ( $i = 0; $i < count( $note_imgs ); $i++ ) { 
                $this->deleteFile('multimedia_notas',$note_imgs[$i]['ID_MULTI']);
            }
        }
        $resp = $this->NotesModel->delete( (int) $id);

        return $resp ? $this->output_json( 200 , 'delete note!')
                     : $this->output_json( 500 , 'have a problem with note deleted!');
    }

}
