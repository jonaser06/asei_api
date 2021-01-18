<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notes extends MY_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('Notes_Model', 'NotesModel');
        $this->load->model('Files_Model','FileModel');

    }
    public function search( $categorie )
    {
        $notes_quanty = 4;

        $section = $this->NotesModel->get_section( [ 'nombre' => $categorie ]);
        if ( !$section ) return $this->output_json(200 , 'Not exists this section' , [] , false );
        
        $params     = $this->input->get(['page', 'limit', 'search'], TRUE);
        if ( !$params['search'] ) return $this->output_json(400 , 'Debe enviar una nota a buscar');
        $search   = explode(' ', $params['search']);
        
        $for_page   = $params['limit'] ? (int) $params['limit'] : $notes_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        
        $conditions = ['notas.ID_SEC' => (int) $section['ID_SEC']];

        $notes = $this->NotesModel->getAll( $for_page ,$offset ,$conditions ,false ,$search );
        if ( !$notes )  return $this->output_json(200 , "not exists results" ,[] ,false );
    
        for( $i = 0; $i < count( $notes['notes'] ) ; $i ++ ): 
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
    public function get_sections() {
        $sections = $this->NotesModel->get_section();
        return $sections ? $this->output_json( 200 , 'sections find !' ,$sections ) 
                         : $this->output_json( 200 , 'no exist any section !' ,[] ,false); 
    }
    public function insert()
    {
           if( ! $this->input->post('titulo') )        return $this->output_json(400 , 'Debe enviar el título');
           if( ! $this->input->post('resumen') )       return $this->output_json(400 , 'Debe enviar el resumen');
           if( ! $this->input->post('texto') )         return $this->output_json(400 , 'Debe enviar el texto');
           if( ! $this->input->post('seccion') )     return $this->output_json(400 , 'Debe enviar la sección');
           if($this->input->post('seccion') == 'noticias') {
            if( ! $this->input->post('fecha_publicacion') )  return $this->output_json(400 , 'Debe enviar la fecha publicacion');
           }else {
               if( ! $this->input->post('fecha_inicio') )  return $this->output_json(400 , 'Debe enviar la fecha de inicio');
               if( ! $this->input->post('fecha_fin') )     return $this->output_json(400 , 'Debe enviar la fecha de final de la nota');
               if( ! $this->input->post('hora_inicio') )   return $this->output_json(400 , 'Debe enviar la hora de inicio');
               if( ! $this->input->post('hora_fin') )      return $this->output_json(400 , 'Debe enviar la hora de finalización');
           }
           if ( empty($_FILES['files']['name']) )      return $this->output_json(400 , 'no select any file');    

           $inputs = $this->input->post(NULL, TRUE);
           $section = $this->NotesModel->get_section( [ 'nombre' => $this->input->post('seccion') ]);
           if( !$section ) return $this->output_json(200 , 'Not exists this section for note' , [] , false );

           $data = [
            'ID_NO'        => $this->generateId(),
            'titulo'       => $inputs['titulo'],
            'resumen'      => $inputs['resumen'],
            'texto'        => $inputs['texto'],
            'ID_SEC'       => (int)$section['ID_SEC'],
           ];

           if( $section['nombre'] == 'noticias') {
               $data['fecha_inicio']    = $inputs['fecha_publicacion'];
               $data['fecha_fin']       = $inputs['fecha_publicacion'];
               $data['hora_inicio']     = '00:00';
               $data['hora_fin']        = '00:00';
               $data['FECHA_PUBLISHED'] = $inputs['fecha_publicacion'];
           }else {
               $data['fecha_inicio']    = $inputs['fecha_inicio'];
               $data['fecha_fin']       = $inputs['fecha_fin'];
               $data['hora_inicio']     = $inputs['hora_inicio'];
               $data['hora_fin']        = $inputs['hora_fin'];
           }

           $note = $this->NotesModel->insert($data);
           if( !$note ) return $this->output_json(400 , 'no se pudo insertar la nota ');
           $multi = $this->create_files('multimedia_notas','ID_NO', (int)$data['ID_NO'] , $_FILES );

           $note  = $this->NotesModel->get( (int)$data['ID_NO']);
           $note_imgs = $this->FileModel->getOne('ID_NO','multimedia_notas',['ID_NO' => (int) $note['ID_NO']]);
           if( !empty($note) ) $note['files'] = $note_imgs;
           return $this->output_json(200 , 'note insert', $note);
    }
    public function getById( int $id )
    {
        $note = $this->NotesModel->get((int) $id);
        if(!$note) return $this->output_json( 200 , 'id is incorrect , not exist note ' , [] , false );
        $note_imgs = $this->FileModel->getOne('ID_NO','multimedia_notas',['ID_NO' => $id]);
        if( !empty($note) ) $note['files'] = $note_imgs;
        $this->output_json( 200 ,'find note!' , $note );
    }
    public function get( $categorie )
    {
        $notes_quanty = 4;

        $section = $this->NotesModel->get_section( [ 'nombre' => $categorie ]);
        if ( !$section ) return $this->output_json(200 , 'Not exists this section' , [] , false );
        
        $params     = $this->input->get(['page', 'limit', 'last', 'search'], TRUE);
        $for_page   = $params['limit'] ? (int) $params['limit'] : $notes_quanty;
        $offset     = $params['page']  ? $for_page * ($params['page'] - 1) : 0;
        $last       = $params['last'] == 'true' ? true :false;
        $conditions = ['notas.ID_SEC' => (int) $section['ID_SEC']];

        $notes = $this->NotesModel->getAll( $for_page ,$offset ,$conditions , $last );
        if ( !$notes )  return $this->output_json(200 , "not exists notes for in section : $categorie" ,[] ,false );
    
        for( $i = 0; $i < count( $notes['notes'] ) ; $i ++ ): 
            $note_imgs = $this->FileModel->getOne('ID_NO','multimedia_notas',['ID_NO' => $notes['notes'][$i]['ID_NO']]);
            $notes['notes'][$i]['imagenes'] = $note_imgs ? $note_imgs : 'no images found';
        endfor;

        $page           = $params['page'] ? (int) $params['page'] : 1 ;
        $notes['page']  = $page;
        $pages          = ($notes['countAll'] % $for_page ) ?   (int)($notes['countAll'] / $for_page) + 1 : (int)$notes['countAll'] / $for_page  ; 
        $notes['pages'] = $pages;
        $section        = $notes['notes'][0]['seccion'];

        if($page > 1) {
            $prev = $page - 1  ;
            $notes['prev'] = "/$section?page=$prev&limit=$for_page";
        } 
        if( $page < $pages ) {
            $next = $page + 1 ;
            $notes['next'] = "/$section?page=$next&limit=$for_page";
        }
       
        $this->output_json( 200 , 'find notes for this section !' , $notes );
    } 
    public function update (int $id)
    {
        $note = $this->NotesModel->get((int) $id);
        if(!$note) return $this->output_json( 200 , 'id is incorrect , not exist note ' , [] , false );

        if( ! $this->input->post('titulo') )        return $this->output_json(400 , 'Debe enviar el título');
        if( ! $this->input->post('resumen') )       return $this->output_json(400 , 'Debe enviar el resumen');
        if( ! $this->input->post('texto') )         return $this->output_json(400 , 'Debe enviar el texto');
        if( ! $this->input->post('fecha_inicio') )  return $this->output_json(400 , 'Debe enviar la fecha_inicio');
        if( ! $this->input->post('fecha_fin') )     return $this->output_json(400 , 'Debe enviar la fecha_fin');
        if( ! $this->input->post('seccion') )       return $this->output_json(400 , 'Debe enviar la seccion');
        if ( empty($_FILES['file']['name']) )      return $this->output_json(400 , 'no select any file');    

        $inputs = $this->input->post(NULL, TRUE);
        $section = $this->NotesModel->get_section( [ 'nombre' => $this->input->post('seccion') ]);
        if( !$section ) return $this->output_json(200 , 'Not exists this section for note' , [] , false );
        $set = [
         'titulo'       => $inputs['titulo'],
         'resumen'      => $inputs['resumen'],
         'texto'        => $inputs['texto'],
         'fecha_inicio' => $inputs['fecha_inicio'],
         'fecha_fin'    => $inputs['fecha_fin'],
         'ID_SEC'       => (int)$section['ID_SEC'],
        ]; 
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
