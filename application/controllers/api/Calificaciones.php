<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calificaciones extends MY_Controller {

    public function __construct()
    {
        parent::__construct();
		    $this->load->model('calificaciones_model', 'CalificacionesModel');
    } 

    private function decorador_prom(array $registros ): array {
        
        $registro_depurado = array_map(function($registro){

            $registro["PROMEDIO_ESTRELLAS"]=(double)number_format((float)$registro["PROMEDIO_ESTRELLAS"],1);

            return $registro ; 

        },$registros);

        return $registro_depurado ;

        
    }

    private function decorador_suma(array $registros ): array {
        
        $registro_depurado = array_map(function($registro){

            $registro["SUMA_ESTRELLAS"]=(double)number_format((float)$registro["SUMA_ESTRELLAS"],1);

            return $registro ; 

        },$registros);

        return $registro_depurado ;

        
    }

    public function getAllProm() : CI_Output
    {
      $calificicaciones = $this->CalificacionesModel->getAllPromedio();
        if( !$calificicaciones ) return $this->output_json(200,'No se encontraron resultados',[] , false);
        $califi_prom_dec = $this->decorador_prom($calificicaciones);
        

        return $this->output_json(200 , 'note find !!' , $califi_prom_dec) ; 
    } 

    public function getAllSuma() : CI_Output
    {
      
      $calificicaciones = $this->CalificacionesModel->getAllSuma(); 
        if( !$calificicaciones ) return $this->output_json(200,'No se encontraron resultados', [] , false); 

        $califi_suma_dec = $this->decorador_suma($calificicaciones);
        

        return $this->output_json(200 , 'note find !!' , $califi_suma_dec) ; 
    } 
    
    public function getByIdProm( int $id )
    {
        $note = $this->CalificacionesModel->getPromedio((int) $id);
        if(!$note) return $this->output_json( 200 , 'id is incorrect , not exist note ' , [] , false );
        
        $note["PROMEDIO_ESTRELLAS"]=(double)number_format((float)$note["PROMEDIO_ESTRELLAS"],1); 
        
        $this->output_json( 200 ,'find note!' , $note ); 
    } 

    public function getByIdSuma( int $id )
    {
        $note = $this->CalificacionesModel->getSuma((int) $id);
        if(!$note) return $this->output_json( 200 , 'id is incorrect , not exist note ' , [] , false );

        $note["SUMA_ESTRELLAS"]=(double)number_format((float)$note["SUMA_ESTRELLAS"],1);
        
        
        $this->output_json( 200 ,'find note!' , $note ); 
    } 
    public function setCalificacion(int $id_nota , int $id_us )
    {
        if ( !$this->input->post('calificacion',TRUE ) ) return $this->output_json( 400 , 'debe enviar la calificación para la nota'); 
    }   
}