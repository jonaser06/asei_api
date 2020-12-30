<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LoginUser extends MY_Controller {

	public function __construct()
    {
		parent::__construct();
		$this->load->model('User_Model', 'UserModel');
    }
	
	/**
     * User Register
     * --------------------------
     * @param: fullname
     * @param: username
     * @param: email
     * @param: password
     * --------------------------
     * @method : POST
     * @link : /register/
     */
	
	
    public function get()
    {
     
    }
    public function update()
    {
     
    }
    public function remove()
    {
     
    }
    public function getAll()
    {
     
    }
	
}
