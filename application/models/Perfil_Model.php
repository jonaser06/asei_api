<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Perfil_Model extends CI_Model
{
    protected $table = 'perfiles';

    /**
     * Perfil Registration
     * @param: {array} perfil Data
     */
    public function insert(array $data = []) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function get(
        ?array $conditions = NULL
    ) {
           return empty($conditions)
            ? $this->db->get($this->table)->result_array()
            : $this->db->get_where($this->table, $conditions)->row_array();
    }
 
}
