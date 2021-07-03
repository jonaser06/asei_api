<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Type_Activity extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Type_Activity_Model', 'TypeActivityModel');
	}

	public function get()
	{
		$type_activity_quantity = 9;
		$params = $this->input->get(['page', 'limit', 'last', 'search'], TRUE);
		$for_page = $params['limit'] ? (int)$params['limit'] : $type_activity_quantity;
		$offset = $params['page'] ? $for_page * ($params['page'] - 1) : 0;

		$type_activities = $this->TypeActivityModel->getAll($for_page, $offset, []);

		$page = $params['page'] ? (int)$params['page'] : 1;
		$type_activities['page'] = $page;
		$pages = ($type_activities['countAll'] % $for_page) ? (int)($type_activities['countAll'] / $for_page) + 1 : (int)$type_activities['countAll'] / $for_page;
		$type_activities['pages'] = $pages;

		if ($page > 1) {
			$prev = $page - 1;
			$type_activities['prev'] = "?page=$prev&limit=$for_page";
		}
		if ($page < $pages) {
			$next = $page + 1;
			$type_activities['next'] = "?page=$next&limit=$for_page";
		}

		$this->output_json(200, 'find type_activities !', $type_activities);
	}
}
