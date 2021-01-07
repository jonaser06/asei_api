<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_Model extends CI_Model
{
    protected $table = 'usuarios';

    
    /**
     * User Registration
     * @param: {array} User Data para insertar al DB
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

    public function getALL(int $limit = 1, int $offset = 0 )
    {
        $this->db->select('ID_US, NOMBRES,APELLIDO_PATERNO,APELLIDO_MATERNO,EMAIL,p.TIPO');
        $this->db->from('usuarios as us');
        $this->db->join('perfiles as p', 'p.ID_PE = us.ID_PE');
        $users = $this->db->get()->result_array();
        $this->db->limit($limit, $offset);
        return $users ? $users : FALSE;
    }
    public function get($id)
    {
        $this->db->select('ID_US, NOMBRES,APELLIDO_PATERNO,APELLIDO_MATERNO,EMAIL,TELEFONO,p.TIPO');
        $this->db->from('usuarios as us');
        $this->db->join('perfiles as p', 'p.ID_PE = us.ID_PE');
        $this->db->where(['ID_US' => (int) $id]);
        $user = $this->db->get()->row_array();
        return $user ? $user : FALSE;
    }
   
   
 
}
