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
    public function insert_categorie( array $data = [] )
    {
        $categorie_doc = $this->db->insert($this->area, $data);
        return $categorie_doc ?$categorie_doc : FALSE ;
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
    public function get_doc(
        ?array $conditions = NULL
    ) {
        $this->db->select('*');
        $result = $conditions 
            ? $this->db->get_where($this->table2 ,$conditions)->row_array()
            : $this->db->get($this->table2)->result_array();
        return !empty($result) ? $result : FALSE;
    }
    public function get_documentsAll(
        ?array $conditions = NULL
    ) {
        $this->db->select('*');
        $result = $this->db->get_where($this->table2 ,$conditions)->result_array();
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
    public function getAll( int $limit = 1, int $offset = 0, array $conditions = [] , bool $lasted = FALSE , array $params  = [] )
    {
        $this->db->select('documentos.id_ar,ID_DOC,documentos.RUTA,FILE_NAME,nombre,TIPO,FECHA_CREATED as fecha_publicacion ,area');
        $this->db->join('area as a' , 'documentos.id_ar = a.id_ar');

        $this->db->where( $conditions );
        if( count ($params) != 0) {
            array_map(function ($param) {
                $this->db->group_start();
                $this->db->or_like('nombre', $param, 'both');
                $this->db->or_like('TIPO', $param, 'both');
                $this->db->group_end();
            }, $params);
        }
        if($lasted) {
            $this->db->where( 'FECHA_CREATED >= (CURDATE() - INTERVAL 30 DAY)');
        }
        $this->db->order_by('FECHA_CREATED', 'DESC');

        $countAll = $this->db->count_all_results('documentos', FALSE);
        $this->db->limit($limit, $offset);
        $documentos = $this->db->get()->result_array();

        return $documentos ? [
            'countAll'     => $countAll,
            'archivos'         => $documentos
        ] : FALSE;
        
    }
    public function get_categories( int $limit = 1, int $offset = 0, array $conditions = [] , bool $lasted = FALSE , array $params  = [] )
    {
        $this->db->select('*');
        if( count ($params) != 0) {
            array_map(function ($param) {
                $this->db->like('area', $param, 'both');
            }, $params);
        }
        $this->db->where( $conditions );
        if($lasted) {
            $this->db->where( 'fecha >= (CURDATE() - INTERVAL 30 DAY)');
        }
        $this->db->order_by('fecha', 'DESC');

        $countAll = $this->db->count_all_results('area', FALSE);
        $this->db->limit($limit, $offset);
        $areas = $this->db->get()->result_array();

        return $areas ? [
            'countAll'     => $countAll,
            'areas'         => $areas
        ] : FALSE;
        
    }
    public function update_categorie (array $set , array $where )
    {
        if( empty($set) ) return false;
        $this->db->set($set);
        $this->db->where($where);
        return  $this->db->update($this->area) ? true : false;
        
    }
    public function update (array $set , array $where )
    {
        if( empty($set) ) return false;
        $this->db->set($set);
        $this->db->where($where);
        return  $this->db->update($this->table) ? true : false;
        
    }
    public function update_doc (array $set , array $where )
    {
        if( empty($set) ) return false;
        $this->db->set($set);
        $this->db->where($where);
        return  $this->db->update($this->table2) ? true : false;
    }
    public function remove( string $table, array $condition ) 
    {
        $result  = $this->db->delete($table,$condition);
        return $result ? true : false;
    }
 
}
