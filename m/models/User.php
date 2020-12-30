<?php 

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
        // $this->db->select('*');
        // $this->db->from('usuarios as us');
        // $this->db->join('perfiles as p', 'p.ID_PE = us.ID_PE');
        // $this->db->where(['EMAIL' => $correo , 'CLAVE' => md5($password)]);
        // $user = $this->db->get()->row_array();
        return $user ? $user : FALSE;
    }
    
    public function getOne(
        ?array $conditions = NULL
    ) {
           return empty($conditions)
            ? $this->db->get($this->table)->result_array()
            : $this->db->get_where($this->table, $conditions)->row_array();
    }
 
}
