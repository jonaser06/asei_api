<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends MY_Controller {

	public function __construct()
    {
		parent::__construct();
		$this->load->model('Notification_Model', 'notification');
        date_default_timezone_set("America/Lima");
    }
    

    public function setNotification(){
        // $date = new DateTime();
        // echo $date->format("Y-m-d H:i:s");
        // exit;
        if(($this->input->server('REQUEST_METHOD') === 'POST')){
            
            $inputJSON = file_get_contents('php://input');
            $input = json_decode($inputJSON, TRUE);

            if ( isset($input['titulo']) && $input['titulo'] == "" ) return $this->output_json(400,'The title is necessary');
            if ( isset($input['descripcion']) && $input['descripcion'] == "" ) return $this->output_json(400,'The description is necessary');
            if ( isset($input['fecha']) && $input['fecha'] == "" ) return $this->output_json(400,'The date is necessary');
            if ( isset($input['destino']) && $input['destino'] == "" ) return $this->output_json(400,'The destination is necessary');
            if ( isset($input['categoria']) && $input['categoria'] == "" ) return $this->output_json(400,'The category is necessary');
            if ( isset($input['ID_US']) && $input['ID_US'] == "" ) return $this->output_json(400,'The ID_US is necessary');
            if ( isset($input['estado']) && $input['estado'] == "" ) return $this->output_json(400,'The state is necessary'); # notificacion 1: Nuevo 2: Leido 3: Eliminado

            $this->data[0] = [
                'titulo'      => $input['titulo'],
                'descripcion' => $input['descripcion'],
                'fecha'       => $input['fecha'],
                'destino'     => $input['destino'],
                'categoria'   => $input['categoria'],
                'ID_US'       => $input['ID_US'],
                'estado'      => $input['estado']
            ];
    
            #an error occurred 
            if( !$this->notification->setdata( $this->data[0] , 'notificaciones' ) ) return $this->output_json(200,'an error occurred while inserting the data');
    
            return $this->output_json(200,'query successfully', $this->data);
        }

    }

    public function getNotification(){
        if(($this->input->server('REQUEST_METHOD') === 'POST')){
            $inputJSON = file_get_contents('php://input');
            $input = json_decode($inputJSON, TRUE);
            if ( isset($input['ID_US']) && $input['ID_US'] == "" ) return $this->output_json(400,'The id user is necessary');

            $ID_US = $input['ID_US'];
            $notes_quanty = 6;
            $page = $input['page'];
            $limit = $input['limit'];

            $match = ( !isset($input['match']) ) ? [] : explode(' ', $input['match']) ;

            $for_page   = $limit ? (int) $limit : $notes_quanty;
            $offset     = $page  ? $for_page * ($page - 1) : 0;
            $page = $page ? (int) $page : 1 ;

            $select = '*';
            $table = 'notificaciones';
            #an error occurred 
            if($match){
                $this->data = $this->notification->searchdata($select, $table, [], $match, 'id_notificacion', $for_page, $offset);
            }else{
                $this->data = $this->notification->getdata($select, $table, [], 'id_notificacion', $for_page, $offset);
            }

            $pages = ($this->data['countAll'] % $for_page ) ?   (int)($this->data['countAll'] / $for_page) + 1 : (int)$this->data['countAll'] / $for_page  ; 
            $this->data['page'] = $page;
            $this->data['pages'] = $pages;
            if( !$this->data ) return $this->output_json(200,'an error occurred while get the dataa');
            return $this->output_json(200,'query successfully', $this->data);
        }
    }
}