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
    
    /**
     * @param $code :código http respuesta
     * @param $resp : cuerpo de respuesta
     */
    public function output_json ( int $code, string $message, $data = [] ) : CI_Output
    {
        $status = ($code >= 200 && $code < 400 ) ? TRUE :FALSE ;
        $this->data = $this->body_data($status, $message, $data, $code);

        return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($this->data));
                
    }
   
    private function body_data( bool $status, string $message, $data, $code ):array
    {
        $this->data = [
            'status'  => $status,
            'code'    => $code,
            'message' => $message, 
        ];
        if($data !== NULL): 
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
}