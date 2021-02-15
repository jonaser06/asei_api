<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/Interfaces/' . 'module_interface.php';
class Notification_Model extends CI_Model implements iModule
{
    public function getdata( $select = '' , $table = '', $where = [], $o = '', $limit = null, $offset = null){
        if(empty($select)) return false;
        $this->db->select($select);
        $this->db->from($table);
        $this->db->where($where);
        $this->db->order_by($o, 'DESC');
        $this->db->limit($limit, $offset);
        $query = $this->db->get()->result_array();
        $countAll = $this->db->count_all_results($table, FALSE);
        if ($query) {
            return [
                'countAll' => $countAll,
                'content'=> $query
            ];
        }
        return false;

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