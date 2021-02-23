<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Contenido_Model extends CI_Model
{
    protected $table = 'contenido';
    private $table_section = 'secciones';
    private $id_mod = 4;
    public function get_section ( array $condition = NUll )  
    {     
        $section = $condition 
                    ? $this->db->get_where( $this->table_section , $condition )->row_array()
                    : $this->db->get_where( $this->table_section , ['ID_MOD' => $this->id_mod])->result_array();
        return $section ? $section : FALSE ;
    }

    public function get( int $id_contenido , $condition = NULL)
    {
        $this->db->select('ID_CO, titulo , resumen , objetivo , duracion , sec.nombre as tipo  ,FECHA_PUBLISHED as fecha_publicacion');
        $this->db->from('contenido');
        $this->db->join('secciones as sec' , 'contenido.ID_SEC = sec.ID_SEC');
        $this->db->where(['contenido.ID_CO ' =>(int) $id_contenido]);
        if( $condition) {
            $this->db->where($condition);
        }
        $learn = $this->db->get()->row_array();
        return $learn ? $learn : FALSE;
    }
    public function get_sesiones ( int $id_contenido)
    {
        $this->db->select('ID_SE ,nombre , link ');
        $this->db->from('sesiones');
        $this->db->where(['sesiones.ID_CO ' =>(int) $id_contenido]);
        $this->db->order_by('FECHA_REGISTRO', 'DESC');

        $sesiones = $this->db->get()->result_array();
        return $sesiones ? $sesiones : FALSE;
    }
    public function get_capacitadores ( int $id_contenido)
    {
        $this->db->select('ID_CA ,nombre , resumen');
        $this->db->from('capacitadores');
        $this->db->where(['capacitadores.ID_CO' =>(int) $id_contenido]);
        $cap = $this->db->get()->result_array();
        return $cap ? $cap : FALSE;
    }
    
    public function getAll( int $limit = 1, int $offset = 0, array $conditions = [] , bool $lasted = FALSE , array $params  = [] )
    {
        
        $this->db->select('ID_CO, titulo , resumen , objetivo , duracion , sec.nombre as tipo  ,FECHA_PUBLISHED as fecha_publicacion ');
        $this->db->join('secciones as sec' , 'contenido.ID_SEC = sec.ID_SEC');
        if( count ($params) != 0) {
            array_map(function ($param) {
                $this->db->like('titulo', $param, 'both');
            }, $params);
        }
        $this->db->where( $conditions );
        if($lasted) {
            $this->db->where( 'FECHA_PUBLISHED >= (CURDATE() - INTERVAL 30 DAY)');
        }
        $this->db->order_by('FECHA_PUBLISHED', 'DESC');

        $countAll = $this->db->count_all_results('contenido', FALSE);
        $this->db->limit($limit, $offset);
        $contenidos = $this->db->get()->result_array();

        return $contenidos ? [
            'countAll'     => $countAll,
            'contenido'         => $contenidos
        ] : FALSE;
        
    }
    public function insert( array $data = [] )
    {
        $contenido = $this->db->insert($this->table, $data);
        return $contenido ?$contenido : false ;
    }
    public function insert_rows (array $data = [] , $table ):bool {
        $result = $this->db->insert_batch( $table , $data);
        return $result ? TRUE : FALSE;
    }
    public function update (array $set , array $where )
    {
        if( empty($set) ) return false;
        $this->db->set($set);
        $this->db->where($where);
        return  $this->db->update($this->table) ? true : false;
        
    }
    public function delete( int $id ) 
    {
        $result  = $this->db->delete($this->table, [ 'ID_CO' => $id ] );
        return $result ? true : false;
    }
    public function remove( string $table, array $condition ) 
    {
        $result  = $this->db->delete($table,$condition);
        return $result ? true : false;
    }
}
