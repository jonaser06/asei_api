<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/Interfaces/' . 'module_interface.php';
class Statistics_Model extends CI_Model implements iModule
{
    // protected $table = 'statistics';

    /**
     * Statistics_Model
     * @param: {array} perfil Data
     */
    // public function insert(array $data = []) {
    //     $this->db->insert($this->table, $data);
    //     return $this->db->insert_id();
    // }
    public function getdata( $data = '' , $table = ''){
        if(empty($data)){
            return false;
        }
        echo 'lleno';
        exit;

        // $this->db->insert($this->table, $data);

    }
    public function setdata( $data = '' , $table = ''){
        if( empty($data) ) return false;
        $query = $this->db->insert($table, $data);
        if ($query) return true;
        return false;

    }
    public function deldata( $data = '' , $table = ''){
        $query = $this->db->delete($table, $data);
        if ($query) return true;
        return false;
    }
    public function upddata( $data = '', $where = '' , $table = ''){
        if( empty($data) ) return false;
        $this->db->set($data);
        $this->db->where($where);
        $query = $this->db->update($table);
        if ($query) return true;
        return false;
    }
}
