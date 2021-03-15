<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Privileges_Model extends CI_Model
{
    protected $table = 'modulos_perfiles';
    protected $table2 = 'modulos';
    protected $table3 = 'modulos_usuarios';

    public function get($id)
    {
        $this->db->select('mod.nombre as modulo , mod.path , mod.icon , CREAR as crear , ACTUALIZAR as actualizar , ELIMINAR as eliminar , VISUALIZAR as visualizar');
        $this->db->from(' modulos_perfiles  as mod_p');
        $this->db->join('perfiles as p', 'p.ID_PE = mod_p.ID_PE');
        $this->db->join('modulos as mod', 'mod.ID_MO = mod_p.ID_MO');
        $this->db->where(['mod_p.ID_PE' => (int) $id]);
        $privileges = $this->db->get()->result_array();
        return $privileges ? $privileges : FALSE;
    }
    public function getAll()
    {
        $this->db->select('p.TIPO as perfil, mod.nombre as modulo , mod.path , mod.icon , CREAR as crear , ACTUALIZAR as actualizar , ELIMINAR as eliminar , VISUALIZAR as visualizar');
        $this->db->from(' modulos_perfiles  as mod_p');
        $this->db->join('perfiles as p', 'p.ID_PE = mod_p.ID_PE');
        $this->db->join('modulos as mod', 'mod.ID_MO = mod_p.ID_MO');
        $privileges = $this->db->get()->result_array();
        return $privileges ? $privileges : FALSE;
    }
    
    public function get_for_user (int $id)
    {
        $this->db->select('mod.nombre as modulo , mod.path , mod.icon , CREAR as crear , ACTUALIZAR as actualizar , ELIMINAR as eliminar , VISUALIZAR as visualizar');
        $this->db->from(' modulos_usuarios  as mod_us');
        $this->db->join('usuarios as us', 'us.ID_US = mod_us.ID_US');
        $this->db->join('modulos as mod', 'mod.ID_MO = mod_us.ID_MO');
        $this->db->where(['mod_us.ID_US' => (int) $id]);
        $privileges = $this->db->get()->result_array();
        return $privileges ? $privileges : FALSE;
    }
    public function get_module ($condition = [])
    {
        $this->db->select('*');
        $this->db->from($this->table2);
        $this->db->where($condition);
        $modules = $this->db->get()->row_array();
        return $modules ? $modules : FALSE;
    }
    public function get_module_user ($condition)
    {
        $this->db->select('*');
        $this->db->where($condition);
        $this->db->from($this->table3);
        $modules_user = $this->db->get()->row_array();
        return $modules_user ? $modules_user : FALSE;
    }
    public function insert_module(array $data = [] ) {
        $this->db->insert($this->table3, $data);
        return $this->db->insert_id();
    }
    public function update_module(array $set , array $where )
    {
            if( empty($set) ) return false;
            $this->db->set($set);
            $this->db->where($where);
            return  $this->db->update($this->table3) ? true : false;  
    }
}
