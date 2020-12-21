<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class MY_Controller extends CI_Controller 
{
    private $data = [];
    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * @param $code :código http respuesta
     * @param $resp : cuerpo de respuesta
     */
    public function output_json ( int $code, string $message, ? array $data = NULL ) : CI_Output
    {
        $status = ($code >= 200 && $code < 400 ) ? TRUE :FALSE ;
        $this->data = $this->body_data($status, $message, $data);

        return $this->output
                ->set_content_type('application/json')
                ->set_status_header($code)
                ->set_output(json_encode($this->data));
                
    }
   
    private function body_data( bool $status, string $message, $data ):array
    {
        $this->data = [
            'status'      => $status,
            'message' => $message 
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
    public function generateId():int {
        return (int)time();
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