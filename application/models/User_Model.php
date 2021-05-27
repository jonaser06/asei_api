<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_Model extends CI_Model
{
    protected $table = 'usuarios';

    /**
     * User Registration
     * @param: {array} User Data
     */
    public function insert_user(array $data = []) {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

   
    /**
     * User Login
     * ----------------------------------
     * @param: email address
     * @param: password
     */
    public function login( string $correo, string $password)
    {
        $this->db->select('*');
        $this->db->from('usuarios as us');
        $this->db->join('perfiles as p', 'p.ID_PE = us.ID_PE');
        $this->db->where(['EMAIL' => $correo , 'CLAVE' => md5($password)]);
        $user = $this->db->get()->row_array();
        return $user ? $user : FALSE;
    }
    public function getOne(
        ?array $conditions = NULL
    ) {
           return empty($conditions)
            ? $this->db->get($this->table)->result_array()
            : $this->db->get_where($this->table, $conditions)->row_array();
    }
    public function get_profile(
        ?array $conditions = NULL
    ) {
           return empty($conditions)
            ? $this->db->get('perfiles')->result_array()
            : $this->db->get_where('perfiles', $conditions)->row_array();
    }
    public function insert( array $data = [] )
    {
        $users = $this->db->insert($this->table, $data);
        return $users ?$users : false ;
    }
    
    public function getALL(int $limit = 1, int $offset = 0, array $conditions = [] , bool $lasted = FALSE , array $params  = [] )
    {
        $this->db->select('ID_US, NOMBRES,APELLIDO_PATERNO,APELLIDO_MATERNO,EMAIL,CARGO,DIRECCION,EMPRESA,FECHA_INGRESO,admin_asociado,usuarios.estado,p.TIPO');
        $this->db->join('perfiles as p', 'p.ID_PE = usuarios.ID_PE');

        if( count ($params) != 0) {
            array_map(function ($param) {
                $this->db->group_start();
                $this->db->or_like('usuarios.NOMBRES', $param, 'both');
                $this->db->or_like('usuarios.APELLIDO_PATERNO', $param, 'both');
                $this->db->or_like('usuarios.APELLIDO_MATERNO', $param, 'both');
                $this->db->group_end();
            }, $params);
        }
        // if( count ($params) != 0) {
        //     array_map(function ($param) {
        //         $this->db->like('usuarios.NOMBRES', $param, 'both');
        //     }, $params);
        // }
        $this->db->where( $conditions );

        $countAll = $this->db->count_all_results('usuarios', FALSE);
        $this->db->limit($limit, $offset);
        $users = $this->db->get()->result_array();
        return $users ? [
            'countAll'     => $countAll,
            'users'        => $users
        ] : FALSE;
    }
    public function get($id)
    {
        $this->db->select('ID_US, NOMBRES,APELLIDO_PATERNO,APELLIDO_MATERNO,EMAIL,TELEFONO,CARGO,DIRECCION,EMPRESA,FECHA_INGRESO,admin_asociado,us.estado,id_notify,p.TIPO');
        $this->db->from('usuarios as us');
        $this->db->join('perfiles as p', 'p.ID_PE = us.ID_PE');
        $this->db->where(['ID_US' => (int) $id]);
        $user = $this->db->get()->row_array();
        return $user ? $user : FALSE;
    }
    public function updateIdNotify(array $set , array $where )
    {
            if( empty($set) ) return false;
            $this->db->set($set);
            $this->db->where($where);
            return  $this->db->update($this->table) ? true : false;  
    }
    
    public function delete( int $id ) 
    {
        $result  = $this->db->delete($this->table, [ 'ID_US' => $id ] );
        return $result ? true : false;
    }
 
}
