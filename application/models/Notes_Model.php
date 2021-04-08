<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notes_Model extends CI_Model
{
    protected $table = 'notas';
    private $table_section = 'secciones';

    public function get_section ( array $condition = NUll )  
    {   
        
        $section = $condition 
                    ? $this->db->get_where( $this->table_section , $condition )->row_array()
                    : $this->db->get_where( $this->table_section , ['ID_MOD' => 3])->result_array();
        return $section ? $section : FALSE ;
    }
  


    public function get( int $id_nota)
    {
        $this->db->select('notas.ID_NO , titulo ,resumen , texto ,link, fecha_inicio , fecha_fin ,sec.nombre as seccion ,hora_inicio ,hora_fin,FECHA_PUBLISHED as fecha_publicacion');
        $this->db->from('notas');
        $this->db->join('secciones as sec' , 'notas.ID_SEC = sec.ID_SEC');
        $this->db->where(['notas.ID_NO ' =>(int) $id_nota]);
        $note = $this->db->get()->row_array();
        return $note ? $note : FALSE;
    }
    
    public function getAll( int $limit = 1, int $offset = 0, array $conditions = [] , bool $lasted = FALSE , array $params  = [] )
    {
        ((int)$conditions['notas.ID_SEC'] == 1 )
        ? $this->db->select('notas.ID_NO  , titulo ,resumen , texto ,sec.nombre as seccion, link, FECHA_PUBLISHED as fecha_publicacion')
        : $this->db->select('notas.ID_NO  , titulo ,resumen , texto , fecha_inicio , fecha_fin ,sec.nombre as seccion ,link,  hora_inicio , hora_fin , FECHA_PUBLISHED as fecha_publicacion ');

        $this->db->join('secciones as sec' , 'notas.ID_SEC = sec.ID_SEC');
        if( count ($params) != 0) {
            array_map(function ($param) {
                $this->db->like('notas.titulo', $param, 'both');
            }, $params);
        }
        
        $this->db->where( $conditions );

        if($lasted) {
            $this->db->where( 'FECHA_PUBLISHED >= (CURDATE() - INTERVAL 30 DAY)');
        }
        // else {
        //     $this->db->where( 'FECHA_PUBLISHED < (CURDATE() - INTERVAL  30 DAY)');

        // }
        $this->db->order_by('FECHA_PUBLISHED', 'DESC');

        $countAll = $this->db->count_all_results('notas', FALSE);
        $this->db->limit($limit, $offset);
        $notes = $this->db->get()->result_array();

        return $notes ? [
            'countAll'     => $countAll,
            'notes'         => $notes
        ] : FALSE;
        
    }
    public function insert( array $data = [] )
    {
        $note = $this->db->insert($this->table, $data);
        return $note ?$note : false ;
    }
    public function update (array $set , array $where )
    {
        if( empty($set) ) return false;
        $this->db->set($set);
        $this->db->where($where);
        return  $this->db->update($this->table) ? true : false;
        
    }
    public function delete( int $id ) 
    {
        $result  = $this->db->delete($this->table, [ 'ID_NO' => $id ] );
        return $result ? true : false;
    }

    public function getAllCalendar( int $limit = 1, int $offset = 0, array $conditions = [] , bool $lasted = FALSE , array $params  = [] )
    {
        
         $this->db->select('notas.ID_NO  , titulo ,resumen , texto , fecha_inicio , fecha_fin ,sec.nombre as seccion ,link,  hora_inicio , hora_fin , FECHA_PUBLISHED as fecha_publicacion ');

        $this->db->join('secciones as sec' , 'notas.ID_SEC = sec.ID_SEC');
        if( count ($params) != 0) {
            array_map(function ($param) {
                $this->db->like('notas.titulo', $param, 'both');
            }, $params);
        }
        
        $this->db->where( $conditions );

        // if($lasted) {
        //     $this->db->where( 'FECHA_PUBLISHED >= (CURDATE() - INTERVAL 30 DAY)');
        // }
        // else {
        //     $this->db->where( 'FECHA_PUBLISHED < (CURDATE() - INTERVAL  30 DAY)');

        // }
        $this->db->order_by('FECHA_PUBLISHED', 'DESC');

        $countAll = $this->db->count_all_results('notas', FALSE);
        $this->db->limit($limit, $offset);
        $notes = $this->db->get()->result_array();

        return $notes ? [
            'countAll'     => $countAll,
            'notes'         => $notes
        ] : FALSE;
        
    }
}

