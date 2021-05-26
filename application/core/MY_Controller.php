<?php
defined('BASEPATH') OR exit('No direct script access allowed');
define('STR_NULL', '');
define('STR_SPACE', ' ');
define('STR_GUION', '-');

require APPPATH . '/libraries/REST_Controller.php';

class MY_Controller extends CI_Controller 
{
    private $data = [];
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('Files_Model','FileModel');
    }

    /**
     * @param $file : file to save
     * @param $name : file name
     * @param $path : file path
     */
    public function fileUpload($file, $name, $path){
        $target = DIR_U . UPLOAD . $path . basename($name);
        if (move_uploaded_file($file, $target)) return $name;
        return false;
    }

    public function newNotification($message= '', $type = '', $idus = '', $id=''){
        date_default_timezone_set('America/Lima');
        // if(($this->input->server('REQUEST_METHOD') === 'POST')){
        //     $inputJSON = file_get_contents('php://input');
        //     $input = json_decode($inputJSON, TRUE);
        // }
        $path = [
            "estadistica" => 'stadistics',
            "indicador" => 'stadistics',
            "boletin" => 'stadistics',
            "aniversarios" => 'infcenter/anniversary/info/',
            "eventos" => 'infcenter/eventos/info/',
            "fairs" => 'infcenter/fairs/info/',
            "news" => 'infcenter/news/info/',
            "cursos" => 'learning-center/cursos/info/',
            "webinnars" => 'learning-center/webinars/info/',
        ];

        $payload = [
            "titulo" => $type,
            "descripcion" => $message,
            "fecha" => date('Y-m-d H:i:s'),
            "destino" => $path[$type].$id,
            "categoria" => $type,
            "ID_US" => $idus,
            "estado" => 1
        ];

        $body =[
            "app_id" => APP_ID,
            "included_segments" => [ "Active Users", "Inactive Users" ],
            "data" => $payload,
            "contents" => [
                "en" => $message,
                "es" => $message
            ],
            "headings" => [
                "en" => $type,
                "es" => $type,
            ]
        ];

        $body = json_encode($body);

        $url = 'https://onesignal.com/api/v1/notifications';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
            'Content-Type:application/json',
            'Authorization:Basic OGIwOGYxYmEtMDYyMS00MDkzLTkwNzktODVhOTE3ZGIxNDIy'
            )
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $status = curl_exec($ch);
        curl_close($ch);

        /**termino de enviar por push */
    }

    public function clearName($title){
        $ac2 = explode(',', 'ñ,Ñ,á,é,í,ó,ú,Á,É,Í,Ó,Ú,ä,ë,ï,ö,ü,Ä,Ë,Ï,Ö,Ü');
        $xc2 = explode(',', 'n,N,a,e,i,o,u,A,E,I,O,U,a,e,i,o,u,A,E,I,O,U');
        $title = strtolower(str_replace($ac2, $xc2, $title));
        $plb = '/\b(a|e|i|o|u|el|en|la|las|es|tras|del|pero|para|por|de|con| ' .
            '.|sera|haber|una|un|unos|los|debe|ser)\b/';
        $title = preg_replace($plb, STR_NULL, $title);
        $title = preg_replace('/[^a-z0-9 -]/', STR_NULL, $title);
        $title = preg_replace('/-/', STR_SPACE, $title);
        $title = trim(preg_replace('/[ ]{2,}/', STR_SPACE, $title));
        $title = str_replace(STR_SPACE, STR_GUION, $title);
        $title = trim($title);
        return $title;
    }
    public function esImagen($path)
    {
        $imageSizeArray = getimagesize($path);
        $imageTypeArray = $imageSizeArray[2];
        return (bool)(in_array($imageTypeArray , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)));
    }
    
    /**
     * @param $code :código http respuesta
     * @param $state : optional state
     * @param $resp : cuerpo de respuesta
     */
    public function output_json ( int $code, string $message, $data = [] ,bool $state = NULL) : CI_Output
    {
        
        if(!isset($state)) {
            $status = ($code >= 200 && $code < 400 ) ? TRUE :FALSE ;
        }else {
            $status = $state;
        }
        $this->data = $this->body_data($status, $message, $data, $code);

        return $this->output
                ->set_content_type('application/json')
                ->set_status_header($code)
                ->set_output(json_encode($this->data));
                
    }
   
    private function body_data( bool $status, string $message, $data, $code ):array
    {
        $this->data = [
            'status'  => $status,
            'code'    => $code,
            'message' => $message, 
        ];
        if(count($data) !== 0 ): 
            $this->data['data'] = $data;
        endif;
        return $this->data;
    }
    
    public function redirige($url = '')
    {
        header('location: ' . $url);
        exit;
    }
    public function generateId() :int {
        $id     = uniqid('' ,TRUE);
        $uniqId = explode('.',$id);
        return (int)($uniqId[1]);
    }
    public function authentication() {
        $headers = $this->input->request_headers();
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateTimestamp($headers['Authorization']);
            if ($decodedToken == false ) {
                return $this->output_json(401,'unnAuthorized');

            }
        }
    }
    /**
     * @param {array} array que contiene elementos a convertir en booleanos
     */
    public function converter_bool(array $convert = [] ) : array
    {
        $array_convert = array_map(function ($module)
        {
            return array_map(function ($e){
                return  $e == '1' ? true : ( $e == '0' ? false : $e);
            },$module);
        },$convert);
        return $array_convert ;
    }
    #CONFIG UPLOAD
    private function configImg () {
        $uploadPath = 'uploads/notes/';

        #congiguramos el upload para cada  file
        $config['allowed_types']  = 'gif|jpg|png|jpeg';
        $config['max_size']       = 50000;
        $config['max_width']      = 3000;
        $config['max_height']     = 3000;
        $config['upload_path']    = $uploadPath;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
    }
    private function configFile () {
        $uploadPath = 'uploads/documents/';
        

        #congiguramos el upload para cada  file
        $config['allowed_types']  = '*';
        $config['max_size']       = 50000;
        $config['max_width']      = 3000;
        $config['max_height']     = 3000;
        $config['upload_path']    = $uploadPath;
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
    }

    /**
     * @param {key} NAME OF ID ENTITY  
     */
    private function files_for_insert( array $files  , string $key_entity , int $id_entidad , $documents = FALSE ,$id_ars = []) : array 
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

            !$documents ? $this->configImg() : $this->configFile();
            date_default_timezone_set("America/Lima");          

            if($this->upload->do_upload('file')){
                $fileData = $this->upload->data();
                $ID_RECURSO = !$documents ? 'ID_MULTI':'ID_DOC';
                $uploadData[$i][$ID_RECURSO]     = $this->generateId();
                $uploadData[$i]['FILE_NAME']     = $fileData['file_name'];
                $uploadData[$i]['RUTA']          =  !$documents ? 'uploads/notes/'.$fileData['file_name'] :'uploads/documents/'.$fileData['file_name']  ;
                $uploadData[$i]['TIPO']          =  !$documents ? 'Imagen' : $fileData['file_ext'];
                $uploadData[$i]['FECHA_CREATED'] = date("Y-m-d H:i:s");
                $uploadData[$i]['MODIFICADO']    = date("Y-m-d H:i:s");
                if($documents):
                    $uploadData[$i]['id_ar'] =$id_ars[$i]['id_ar'] ;
                    $uploadData[$i]['nombre'] =$id_ars[$i]['nombre'] ;
                endif;

                
                if(!empty($id_entidad)):
                    $upload_relation[$i][$ID_RECURSO] = $uploadData[$i][$ID_RECURSO];
                    $upload_relation[$i][$key_entity] = $id_entidad;
                endif;
            }
        }
        return [
            'multimedia' => $uploadData ,
            'relation'   => $upload_relation
        ];
    } 
    public function create_files
    (
        string $table_relation,
        string $id_name,
        int $id_entidad,
        array $files = [] ,
        bool  $documents = FALSE,
        array $id_ars = []
    )
    
    {
        $uploads         = $this->files_for_insert($files , $id_name ,(int)$id_entidad,$documents,$id_ars);
        $uploadData      = $uploads['multimedia'];
        $upload_relation = $uploads['relation'];
        if(!empty($uploadData)) {
            $insert = $this->FileModel->insert($uploadData , $documents);
            if(!empty($upload_relation) && $insert) :
                $response = $this->upload_entidad_multi($upload_relation,$table_relation);
                if( $response ) return $uploadData;
            endif;
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


    public function getAll () 
    {
        $data = $this->FileModel->get();
        return !empty($data) 
            ? $this->output_json(200 , 'Files exits !!' , $data)
            : $this->output_json(200 , 'Not exist files ' , [] , false );
    }
    public function deleteFile(string $table_relation , int $id_file , $documents = FALSE)
    {
        $ID_RECURSO = !$documents ? 'ID_MULTI'  :'ID_DOC';
        $TABLE      = !$documents ? 'multimedia':'documentos';
        $file    =  $this->FileModel->get([$ID_RECURSO => $id_file]);
        if (!$file) return false;
        $path    =  DIR_U . $file['RUTA'];
        $result  =     $this->db->delete($table_relation, [ $ID_RECURSO => $id_file ] );
        if( !$result ) return false;
        $result  =     $this->db->delete($TABLE,[ $ID_RECURSO => $id_file] );
        if( !$result ) return false;

        if( !file_exists($path)) return false;
        unlink($path);
        return true;

    } 
    public function deleteOneFile(int $id_file , $documents = FALSE , $USER = FALSE)
    {
        $ID_RECURSO = !$documents ? 'ID_MULTI'  :'ID_DOC';
        $TABLE      = !$documents ? 'multimedia':'documentos';
        $TABLE_RELATION = $USER ? 'usuarios_documentos' :'gremios_documentos'; 
        $file    =  $this->FileModel->get_entidad($TABLE,[$ID_RECURSO => $id_file]);
        if (!$file) return false;
        $path    =  DIR_U . $file['RUTA'];
        $result  =     $this->db->delete($TABLE_RELATION, [ $ID_RECURSO => $id_file ] );
        if( !$result ) return false;
        $result  =     $this->db->delete($TABLE,[ $ID_RECURSO => $id_file] );
        if( !$result ) return false;
        if( !file_exists($path)) return false;
        unlink($path);
        return true;

    } 
    public function editFileImg (array $files , int $id_file )
    {
    
        $file = $this->FileModel->get(['ID_MULTI' => $id_file]);
        if ( !$file) return false;
        $path    =  DIR_U . $file['RUTA'];
        if( !file_exists($path) ) return false;
        unlink($path);

        $this->configImg();
        if($this->upload->do_upload('file')) $fileData = $this->upload->data();
        date_default_timezone_set("America/Lima");          
        $set = [
            'MODIFICADO' => date("Y-m-d H:i:s"),
            'FILE_NAME'  =>$fileData['file_name'],
            'RUTA'       => 'uploads/notes/'.$fileData['file_name']
        ];
        $result = $this->FileModel->update( $set , ['ID_MULTI' => $id_file ]);
        return $result;
    }
    public function editFile (array $files , int $id_file )
    {

        $_FILES['file']['name']     = $files['files']['name'][0];
        $_FILES['file']['type']     = $files['files']['type'][0];
        $_FILES['file']['tmp_name'] = $files['files']['tmp_name'][0];
        $_FILES['file']['error']    = $files['files']['error'][0];
        $_FILES['file']['size']     = $files['files']['size'][0];
        
        $file = $this->FileModel->get(['ID_MULTI' => $id_file]);
        if ( !$file) return false;
        $path    =  DIR_U . $file['RUTA'];
        $rutas = explode('/',$path);
        if( file_exists($path)&& array_pop($rutas) !== ''  ) unlink($path);
        $this->configImg();
        if($this->upload->do_upload('file')) $fileData = $this->upload->data();
        date_default_timezone_set("America/Lima");          
        $set = [
            'MODIFICADO' => date("Y-m-d H:i:s"),
            'FILE_NAME'  =>$fileData['file_name'],
            'RUTA'       => 'uploads/notes/'.$fileData['file_name']
        ];
        $result = $this->FileModel->update( $set , ['ID_MULTI' => $id_file ]);
        return $result;
    }
    public function editFileDoc (array $files , int $id_file , $nombre = NULL)
    {

        $_FILES['file']['name']     = $files['files']['name'][0];
        $_FILES['file']['type']     = $files['files']['type'][0];
        $_FILES['file']['tmp_name'] = $files['files']['tmp_name'][0];
        $_FILES['file']['error']    = $files['files']['error'][0];
        $_FILES['file']['size']     = $files['files']['size'][0];
        
        $file = $this->FileModel->get_doc(['ID_DOC' => $id_file]);
        if ( !$file) return false;
        $path    =  DIR_U . $file['RUTA'];
        $rutas = explode('/',$path);
        if( file_exists($path)&& array_pop($rutas) !== ''  ) unlink($path);
        $this->configFile();
        if($this->upload->do_upload('file')) $fileData = $this->upload->data();
        date_default_timezone_set("America/Lima");          
        $set = [
            'MODIFICADO' => date("Y-m-d H:i:s"),
            'FILE_NAME'  =>$fileData['file_name'],
            'RUTA'       => 'uploads/documents/'.$fileData['file_name'],
            'TIPO' =>  $fileData['file_ext']
        ];
        if( $nombre ):
            $set['nombre'] = $nombre;
        endif;
        $result = $this->FileModel->update_doc( $set , ['ID_DOC' => $id_file ]);
        return $result;
    }
}