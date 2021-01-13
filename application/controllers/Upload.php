<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('File_model','FileModel');
        $this->load->helper(['form', 'url']);
    }

    private function configImg () {
        $uploadPath = 'uploads/';

        #congiguramos el upload para cada  file
        $config['allowed_types']  = 'gif|jpg|png';
        $config['max_size']       = 100;
        $config['max_width']      = 1024;
        $config['max_height']     = 768;
        $config['upload_path']    = $uploadPath;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
    }

    /**
     * @param {key} NAME OF ID ENTITY  
     */
    private function files_for_insert( array $files  , string $key_entity , int $id_entidad) : array 
    {
        $uploadData = [];
        $upload_relation = [];
        for($i = 0; $i < count($files['files']['name']); $i++){
            #construimos 
            $_FILES['file']['name']     = $files['files']['name'][$i];
            $_FILES['file']['type']     = $files['files']['type'][$i];
            $_FILES['file']['tmp_name'] = $files['files']['tmp_name'][$i];
            $_FILES['file']['error']    = $files['files']['error'][$i];
            $_FILES['file']['size']     = $files['files']['size'][$i];
            #fin contructor

            $this->configImg();
            date_default_timezone_set("America/Lima");          

            if($this->upload->do_upload('file')){
                $fileData = $this->upload->data();
                $uploadData[$i]['ID_MULTI']      = $this->generateId();
                $uploadData[$i]['FILE_NAME']     = $fileData['file_name'];
                $uploadData[$i]['RUTA']          = 'uploads/'.$fileData['file_name'];
                $uploadData[$i]['TIPO']          =  'Imagen';
                $uploadData[$i]['FECHA_CREATED'] = date("Y-m-d H:i:s");
                $uploadData[$i]['MODIFICADO']    = date("Y-m-d H:i:s");
                
                if(!empty($id_entidad)):
                    $upload_relation[$i]['ID_MULTI'] = $uploadData[$i]['ID_MULTI'];
                    $upload_relation[$i][$key_entity] = $id_entidad;
                endif;
            }
        }
        return [
            'multimedia' => $uploadData ,
            'relation'   => $upload_relation
        ];
    } 

    private function create
    (
        string $id_name,
        int $id_entidad,
        array $files = [] 
    )
    {
      

        $uploads = $this->files_for_insert($files , $id_name ,(int)$id_entidad );
        $uploadData = $uploads['multimedia'];
        $upload_relation = $uploads['relation'];

        if(!empty($uploadData)) {
            $insert = $this->FileModel->insert($uploadData);
            if(!empty($upload_relation) && $insert) :
                $response = $this->upload_entidad_multi($upload_relation,'multimedia_usuarios');
            endif;
            return $this->output_json(200,'RESPONS0 ', $uploadData);

        }

    }
    public function index()
    {
        var_dump($_FILES);exit();
        if(!empty($_FILES['files']['name'])){
            $id_entidad = $this->input->post('id', TRUE);

            if( !isset ($id_entidad )) return $this->output_json(400 , 'this Id is necesary ');
            if(!empty($id_entidad) ) :
                if(!$this->exist_entidad('usuarios', ['ID_US' => $id_entidad ])):
                    return $this->output_json(400, 'Id inválido , no es posible relacionar un archivo a este Id');
                endif;
            endif;
            if($id_entidad == "" && $id_entidad !== NULL ) return $this->output_json(400, 'El parametro Id esta vacío');

            $this->create('ID_US', $id_entidad , $_FILES );
        }
    }
    /**
     * @param {$keys_relation}:keys de las entidades
     * @param {$table_relation}: table de relacion entity1_entity_2
     */
    private function upload_entidad_multi(
        array $keys_relation = [],
        string $table_relation) 
    {
        return  $this->FileModel->insert_relation($keys_relation ,$table_relation);
    }

    private function exist_entidad (string $table_entidad , array $condition = []) 
    {   
        $entidad = $this->FileModel->get_entidad($table_entidad , $condition);
        return !empty($entidad) ? TRUE : FALSE;
    }


    public function get (int $id) 
    {   
        $data = $this->FileModel->getOne('ID_US','multimedia_usuarios',['ID_US' => $id]);
        return !empty($data) 
            ? $this->output_json(200 , 'find archive !!' , $data)
            : $this->output_json(200 , 'Not exist File' , [] , false );
        // borrar registro multimedia
        // borrar registro relacion table 
        // borrar file en el servidor

    }
    public function getAll () 
    {
        $data = $this->FileModel->get();
        return !empty($data) 
            ? $this->output_json(200 , 'Files exits !!' , $data)
            : $this->output_json(200 , 'Not exist files ' , [] , false );
        // borrar registro multimedia
        // borrar registro relacion table 
        // borrar file en el servidor

    }
    public function delete (int $id_entidad , int $id_file )
    {
        $file    =     $this->FileModel->get(['ID_MULTI' => $id_file]);
        if (!$file) return $this->output_json(200 , 'not exist register' , [] , false );
        $path    =  DIR_U . $file['RUTA'];
        $result  =     $this->db->delete('multimedia_usuarios', [ 'ID_MULTI' => $id_file ] );
        if( !$result ) $this->output_json(200 , 'not delete file' , [] , false );
        $result  =     $this->db->delete('multimedia', [ 'ID_MULTI' => $id_file] );
        if( !$result ) $this->output_json(200 , 'not delete file ' , [] , false );

        if( !file_exists($path)) return $this->output_json(200 , 'not exist archive in this server' , [] , false );
        unlink($path);
        return $this->output_json(200 , 'delete file'  );

    }
    public function edit ( int $id_file )
    {

        if ( empty($_FILES['file']['name']) )  return $this->output_json(400 , 'no select any file');
        $file = $this->FileModel->get(['ID_MULTI' => $id_file]);
        if ( !$file) return $this->output_json(200 , 'not exist register' , [] , false );
        
        $path    =  DIR_U . $file['RUTA'];
        if( !file_exists($path) ) return $this->output_json(200 , 'not exist archive in this server' , [] , false );
        unlink($path);

        $this->configImg();
        if($this->upload->do_upload('file')) $fileData = $this->upload->data();
        date_default_timezone_set("America/Lima");          
        $set = [
            'MODIFICADO' => date("Y-m-d H:i:s"),
            'FILE_NAME'  =>$fileData['file_name'],
            'RUTA'       => 'uploads/'.$fileData['file_name']
        ];
        $result = $this->FileModel->update( $set , ['ID_MULTI' => $id_file ]);
        if( $result ) $this->output_json( 400 , 'no se pudo actualizar');
        return $this->output_json(200 , 'update file'  );

    }

}