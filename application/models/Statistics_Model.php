<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/Interfaces/' . 'module_interface.php';
class Statistics_Model extends CI_Model implements iModule
{
    protected $table = 'statistics';

    /**
     * Statistics_Model
     * @param: {array} perfil Data
     */
    // public function insert(array $data = []) {
    //     $this->db->insert($this->table, $data);
    //     return $this->db->insert_id();
    // }
   
}
