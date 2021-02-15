<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notification extends MY_Controller {

	public function __construct()
    {
		parent::__construct();
		$this->load->model('Notification_Model', 'notification');
    }

    public function setNotification(){

        if ( !$this->input->post('titulo') ) return $this->output_json(400,'The title is necessary');
        if ( !$this->input->post('descripcion') ) return $this->output_json(400,'The description is necessary');
        if ( !$this->input->post('fecha') ) return $this->output_json(400,'The date is necessary');
        if ( !$this->input->post('destino') ) return $this->output_json(400,'The destination is necessary');
        if ( !$this->input->post('categoria') ) return $this->output_json(400,'The category is necessary');
        if ( !$this->input->post('ID_US') ) return $this->output_json(400,'The ID_US is necessary');
        if ( !$this->input->post('estado') ) return $this->output_json(400,'The state is necessary'); # notificacion 1: Nuevo 2: Leido 3: Eliminado

        $this->data[0] = [
            'titulo'      => $this->input->post('titulo'),
            'descripcion' => $this->input->post('descripcion'),
            'fecha'       => $this->input->post('fecha'),
            'destino'     => $this->input->post('destino'),
            'categoria'   => $this->input->post('categoria'),
            'ID_US'       => $this->input->post('ID_US'),
            'estado'      => $this->input->post('estado')
        ];

        #an error occurred 
        if( !$this->notification->setdata( $this->data[0] , 'notificaciones' ) ) return $this->output_json(200,'an error occurred while inserting the data');

        return $this->output_json(200,'query successfully', $this->data);
    }
}