<?php defined('BASEPATH') OR exit('No direct script access allowed'); 

class Calificaciones_model extends CI_Model {

    protected $table = 'usuarios_notas';
    
    public function get_calification_us (int $id_no , int $id_us ) {
        $calificacion = $this->db->get_where($this->table, [ 'ID_NO' => $id_no , 'ID_US' => $id_us] )->row_array();
        return $calificacion ? $calificacion : FALSE;
    }

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
    
 
   
    public function insert( array $data = [] )
    {
        $calificacion = $this->db->insert($this->table, $data);
        return $calificacion ? $calificacion : false ;
    }

    public function delete( int $id_no , int $id_us ) 
    {

        $result  = $this->db->delete($this->table, [ 'ID_NO' => $id_no , 'ID_US' => $id_us] );
        return $result ? true : false;
    } 

    


}