<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/interfaces/' . 'module_interface.php';

class Statistics extends MY_Controller implements iModule {

	public function __construct()
    {
		parent::__construct();
		$this->load->model('statistics_model', 'statistics');
    }

    /**
     * New Chart
     * --------------------------
     * @param: title
     * @param: description
     * @param: month
     * @param: year
     * @param: file
     * @param: image
     * --------------------------
     * @method : POST
     * @link : /newchart/
     */
    public function newchart()
	{
        echo 'hola mundo';
    }

    public function get(){

    }
    public function set(){

    }
    public function del(){

    }
    public function upd(){

    }
}