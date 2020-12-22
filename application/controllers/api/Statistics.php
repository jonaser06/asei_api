<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Statistics extends MY_Controller {

	public function __construct()
    {
		parent::__construct();
		$this->load->model('Statistics_Model', 'statistics');
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

}