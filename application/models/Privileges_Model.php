<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Privileges_Model extends CI_Model
{
    protected $table = 'modulos_perfiles';

  
   
    public function get($id)
    {
        $this->db->select('mod.nombre as módulo , CREAR as crear , ACTUALIZAR as actualizar , ELIMINAR as eliminar , VISUALIZAR as visualizar');
        $this->db->from(' modulos_perfiles  as mod_p');
        $this->db->join('perfiles as p', 'p.ID_PE = mod_p.ID_PE');
        $this->db->join('modulos as mod', 'mod.ID_MO = mod_p.ID_MO');
        $this->db->where(['mod_p.ID_PE' => (int) $id]);
        $privileges = $this->db->get()->result_array();
        return $privileges ? $privileges : FALSE;
    }
   
 
}
