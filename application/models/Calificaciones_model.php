<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

class Calificaciones_model extends CI_Model {

    protected $table = 'usuarios_notas';
    

    public function getAllPromedio()
    { 
        $this->db->select('notas.ID_NO AS ID_NOTA , notas.titulo AS TITULO_NOTA , notas.resumen AS RESUMEN_NOTA , notas.FECHA_PUBLISHED AS FECHA_PUBLICACION'); 
        $this->db->select_avg("usuarios_notas.cantidad" , "PROMEDIO_ESTRELLAS");  
        $this->db->from('notas'); 
        $this->db->join('usuarios_notas', 'usuarios_notas.ID_NO = notas.ID_NO');
        $this->db->join('usuarios', 'usuarios_notas.ID_US = usuarios.ID_US'); 
        $this->db->group_by('ID_NOTA'); 
        $calificicaciones = $this->db->get()->result_array();
        return $calificicaciones ? $calificicaciones : FALSE;
    } 

    public function getAllSuma()
    {
        $this->db->select('notas.ID_NO AS ID_NOTA , notas.titulo AS TITULO_NOTA , notas.resumen AS RESUMEN_NOTA , notas.FECHA_PUBLISHED AS FECHA_PUBLICACION'); 
        $this->db->select_sum("usuarios_notas.cantidad" , "SUMA_ESTRELLAS");  
        $this->db->from('notas'); 
        $this->db->join('usuarios_notas', 'usuarios_notas.ID_NO = notas.ID_NO');
        $this->db->join('usuarios', 'usuarios_notas.ID_US = usuarios.ID_US'); 
        $this->db->group_by('ID_NOTA'); 
        $calificicaciones = $this->db->get()->result_array();
        return $calificicaciones ? $calificicaciones : FALSE;
    } 

    public function getPromedio($id)
    {
        $this->db->select('notas.ID_NO AS ID_NOTAS , notas.titulo AS TITULO_NOTA , notas.resumen AS RESUMEN_NOTA , notas.FECHA_PUBLISHED AS FECHA_PUBLICACION');
        $this->db->select_avg("usuarios_notas.cantidad" , "PROMEDIO_ESTRELLAS"); 
        $this->db->from('notas');
        $this->db->join('usuarios_notas', 'usuarios_notas.ID_NO = notas.ID_NO');
        $this->db->join('usuarios', 'usuarios_notas.ID_US = usuarios.ID_US'); 
        $this->db->group_by('ID_NOTAS'); 
        $this->db->having(['ID_NOTAS' => (int) $id]); 
        $note = $this->db->get()->row_array(); 
        return $note ? $note : FALSE;
    } 

    public function getSuma($id)
    {
        $this->db->select('notas.ID_NO AS ID_NOTAS , notas.titulo AS TITULO_NOTA , notas.resumen AS RESUMEN_NOTA , notas.FECHA_PUBLISHED AS FECHA_PUBLICACION');
        $this->db->select_sum("usuarios_notas.cantidad" , "SUMA_ESTRELLAS"); 
        $this->db->from('notas');
        $this->db->join('usuarios_notas', 'usuarios_notas.ID_NO = notas.ID_NO');  
        $this->db->join('usuarios', 'usuarios_notas.ID_US = usuarios.ID_US'); 
        $this->db->group_by('ID_NOTAS'); 
        $this->db->having(['ID_NOTAS' => (int) $id]); 
        $note = $this->db->get()->row_array(); 
        return $note ? $note : FALSE;
    } 
    
    public function setVotar($id_us , $id_no )
    {
        $this->db->select('mod.nombre as modulo , mod.path , mod.icon , CREAR as crear , ACTUALIZAR as actualizar , ELIMINAR as eliminar , VISUALIZAR as visualizar');
        $this->db->from(' modulos_perfiles  as mod_p');
        $this->db->join('perfiles as p', 'p.ID_PE = mod_p.ID_PE');
        $this->db->join('modulos as mod', 'mod.ID_MO = mod_p.ID_MO');
        $this->db->where(['mod_p.ID_PE' => (int) $id_us , 'mod_p.ID_PE' => (int) $id_no]);
        $privileges = $this->db->get()->result_array();
        return $privileges ? $privileges : FALSE;
    }

    public function insert( array $data = [] )
    {
        $note = $this->db->insert($this->table, $data);
        return $note ?$note : false ;
    }

    public function delete( int $id_no , int $id_us ) 
    {
        $result  = $this->db->delete($this->table, [ 'ID_NO' => $id_no , 'ID_US' => $id_us] );
        return $result ? true : false;
    } 

    


}