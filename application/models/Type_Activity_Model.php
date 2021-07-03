<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Type_Activity_Model extends CI_Model
{
	protected $table = 'tipo_actividad';

	public function getAll(int $limit = 1, int $offset = 0, array $conditions = [])
	{
		$this->db->where($conditions);

		$countAll = $this->db->count_all_results($this->table, FALSE);
		$this->db->limit($limit, $offset);

		$type_activities = $this->db->get()->result_array();

		return $type_activities ? [
			'countAll' => $countAll,
			'type_activities' => $type_activities
		] : FALSE;
	}

}
