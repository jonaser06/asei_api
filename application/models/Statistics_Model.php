<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Statistics_Model extends CI_Model
{
    protected $table = 'statistics';

    /**
     * Statistics_Model
     * @param: {array} perfil Data
     */
    public function insert(array $data = []) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }
 
}
