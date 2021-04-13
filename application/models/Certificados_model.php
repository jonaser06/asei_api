<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

class Certificados_model extends CI_Model {

    protected $table = 'certificados';
    
    public function get_certificado_us (int $id_co , int $id_us ) {
        $certificado = $this->db->get_where($this->table, [ 'ID_CO' => $id_co , 'ID_US' => $id_us] )->row_array();
        return $certificado ? $certificado : FALSE;
    }
    public function get_certificados_us ( int $id_us ) {
        $certificados = $this->db->get_where($this->table, [ 'ID_US' => $id_us ])->result_array();
        return $certificados ? $certificados : FALSE;
    }
    public function getAll( int $limit = 1, int $offset = 0, array $conditions = [] , bool $lasted = FALSE , array $params  = [] )
    {
        
        $this->db->select('*');
        if( count ($params) != 0) {
            array_map(function ($param) {
                $this->db->like('curse_name', $param, 'both');
            }, $params);
        }
        $this->db->where( $conditions );
        
        $this->db->order_by('fecha_emited', 'DESC');

        $countAll = $this->db->count_all_results('contenido', FALSE);
        $this->db->limit($limit, $offset);
        $contenidos = $this->db->get()->result_array();

        return $contenidos ? [
            'countAll'     => $countAll,
            'contenido'         => $contenidos
        ] : FALSE;
        
    }

    
    
 
   
    public function insert( array $data = [] )
    {
        $certificado = $this->db->insert($this->table, $data);
        return $certificado ? $certificado : false ;
    }

    public function delete( int $id_co , int $id_us ) 
    {
        $result  = $this->db->delete($this->table, [ 'ID_NO' => $id_co , 'ID_US' => $id_us] );
        return $result ? true : false;
    } 

    


}