<?php defined('BASEPATH') OR exit('No direct script access allowed');

class File_model extends CI_Model
{
    protected $table = 'multimedia';

    /**
     * Upload subida de archivos
     * @param: {array} imagenes Data
     * @param: {$relacion: array} : llaves primarias de las entidades
     */
    public function insert(array $uploadData = [] ) :bool{

        $uploads = $this->db->insert_batch($this->table, $uploadData);
        return $uploads ? TRUE : FALSE;
    }

    public function insert_relation (array $data_relation = [] , $table_relation ):bool {
        $uploads = $this->db->insert_batch( $table_relation , $data_relation);
        return $uploads ? TRUE : FALSE;
    }
    
    public function get(
        ?array $conditions = NULL
    ) {
        $this->db->select('ID_MULTI,TIPO,FILENAME,FECHA_CREATED');
        $this->db->from($this->table);
        $result = $conditions 
            ? $this->db->get_where($conditions)->row_array()
            : $this->db->order_by('FECHA_CREATED'.'desc')
                        ->get()->result_array();
        return empty($result) ? $result : FALSE;
    }
    
    public function get_entidad (string $table , array $conditions = []) {
        return $this->db->get_where($table, $conditions)->row_array();
    }
 
}
