<?php
define('STR_NULL', '');
define('STR_SPACE', ' ');
define('STR_GUION', '-');


class Base_Controller 
{
    private $data = [];
    function __construct()
    {
        $this->load->database();
    }
    /**
     * @param $code :código http respuesta
     * @param $resp : cuerpo de respuesta
     */
    public function output_json ( int $code, string $message, $data = [] ) 
    {
        $status = ($code >= 200 && $code < 400 ) ? TRUE :FALSE ;
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
    
}