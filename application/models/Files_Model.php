<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Files_Model extends CI_Model
{
    protected $table = 'multimedia';
    protected $table2= 'documentos';
    protected $area= 'area';

    /**
     * Upload subida de archivos
     * @param: {array} imagenes Data
     * @param: {$relacion: array} : llaves primarias de las entidades
     */
    public function insert(array $uploadData = [], $documents = FALSE ) :bool{

        $uploads = !$documents 
                    ? $this->db->insert_batch($this->table, $uploadData)
                    : $this->db->insert_batch($this->table2, $uploadData);

        return $uploads ? TRUE : FALSE;
    }
    public function insert_relation (array $data_relation = [] , $table_relation ):bool {
        $uploads = $this->db->insert_batch( $table_relation , $data_relation);
        return $uploads ? TRUE : FALSE;
    }
    public function get(
        ?array $conditions = NULL
    ) {
        $this->db->select('*');
        $result = $conditions 
            ? $this->db->get_where($this->table ,$conditions)->row_array()
            : $this->db->get($this->table)->result_array();
        return !empty($result) ? $result : FALSE;
    }
    public function get_entidad (string $table , array $conditions = []) {
        return $this->db->get_where($table, $conditions)->row_array();
    }

    public function getOne( 
        string $id_name,
        string $table,
        ?array $conditions,
        $documents = FALSE 
    )
    {
        $this->db->select('*');
        $this->db->from($table);
        !$documents ? $this->db->join('multimedia as m', 'm.ID_MULTI ='.$table.'.ID_MULTI') : $this->db->join('documentos as d', 'd.ID_DOC ='.$table.'.ID_DOC');
        $this->db->where($conditions);
        $files = $this->db->get()->result_array();
        return $files ? $files : FALSE;
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
    public function update (array $set , array $where )
    {
        if( empty($set) ) return false;
        $this->db->set($set);
        $this->db->where($where);
        return  $this->db->update($this->table) ? true : false;
        
    }
 
}
