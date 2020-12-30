<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('File_model','FileModel');
        $this->load->helper(['form', 'url']);
    }

    public function index()
    {
        if(!empty($_FILES['userFiles']['name'])){
            
            $id_entidad = $this->input->post('id', TRUE);

            if(!empty($id_entidad) ) :
                if(!$this->exist_entidad('usuarios', ['ID_US' => $id_entidad])):
                    return $this->output_json(400, 'Id inválido , no es posible relacionar un archivo a este Id');
                endif;
            endif;

            if($id_entidad == "" && $id_entidad !== NULL ) return $this->output_json(400, 'El parametro Id esta vacío');

            $filesCount = count($_FILES['userFiles']['name']);
            for($i = 0; $i < $filesCount; $i++){
                #construimos 
                $_FILES['userFile']['name']     = $_FILES['userFiles']['name'][$i];
                $_FILES['userFile']['type']     = $_FILES['userFiles']['type'][$i];
                $_FILES['userFile']['tmp_name'] = $_FILES['userFiles']['tmp_name'][$i];
                $_FILES['userFile']['error']    = $_FILES['userFiles']['error'][$i];
                $_FILES['userFile']['size']     = $_FILES['userFiles']['size'][$i];
                #fin contructor

                $uploadPath = 'uploads/';

                #congiguramos el upload por cada file
                $config['allowed_types']  = 'gif|jpg|png';
                $config['max_size']       = 100;
                $config['max_width']      = 1024;
                $config['max_height']     = 768;
                $config['upload_path']    = $uploadPath;
                
                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if($this->upload->do_upload('userFile')){
                    $fileData = $this->upload->data();
                    $uploadData[$i]['ID_MULTI']      = $this->generateId();
                    $uploadData[$i]['FILE_NAME']     = $fileData['file_name'];
                    $uploadData[$i]['RUTA']          = $uploadPath.$fileData['file_name'];
                    $uploadData[$i]['TIPO']          =  'Imagen';
                    $uploadData[$i]['FECHA_CREATED'] = date("Y-m-d H:i:s");
                    $uploadData[$i]['MODIFICADO']    = date("Y-m-d H:i:s");
                    
                    if(!empty($id_entidad)):
                        $upload_relation[$i]['ID_MULTI'] = $uploadData[$i]['ID_MULTI'];
                        $upload_relation[$i]['ID_US'] = $id_entidad;
                    endif;
                }
            }

            if(!empty($uploadData)) {

                $insert = $this->FileModel->insert($uploadData);
                if(!empty($upload_relation) && $insert) :
                    $response = $this->upload_entidad_multi($upload_relation,'multimedia_usuarios');
                    return var_dump($response);
                endif;
                return $this->output_json(200,'RESPONS0 ', $uploadData);

            }
        }
    }
    /**
     * @param {$id_entidad}: uniqId table entidad
     * @param {$id_multi}: uniqId table multimedia
     * @param {$table_relation}: table de relacion 
     */
    private function upload_entidad_multi(
        array $data = [],
        string $table_relation) 
    {
        return  $this->FileModel->insert_relation($data ,$table_relation);
    }

    private function exist_entidad (string $table_entidad , array $condition = []) 
    {   
        $entidad = $this->FileModel->get_entidad($table_entidad , $condition);
        return !empty($entidad) ? TRUE : FALSE;
    }

}