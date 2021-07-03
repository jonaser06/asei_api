<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Activity extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Activity_Model', 'ActivityModel');
		$this->load->model('Group_Model', 'GroupModel');
	}

	public function get()
	{
		$activity_quantity = 9;
		$params = $this->input->get(['page', 'limit', 'last', 'search'], TRUE);
		$for_page = $params['limit'] ? (int)$params['limit'] : $activity_quantity;
		$offset = $params['page'] ? $for_page * ($params['page'] - 1) : 0;

		$activities = $this->ActivityModel->getAll($for_page, $offset, []);

		$page = $params['page'] ? (int)$params['page'] : 1;
		$activities['page'] = $page;
		$pages = ($activities['countAll'] % $for_page) ? (int)($activities['countAll'] / $for_page) + 1 : (int)$activities['countAll'] / $for_page;
		$activities['pages'] = $pages;

		if ($page > 1) {
			$prev = $page - 1;
			$activities['prev'] = "?page=$prev&limit=$for_page";
		}
		if ($page < $pages) {
			$next = $page + 1;
			$activities['next'] = "?page=$next&limit=$for_page";
		}

		$this->output_json(200, 'find activities !', $activities);
	}

	public function getById(int $groupId): CI_Output
	{
		$activity = $this->ActivityModel->get($groupId);
		if (!$activity) {
			return $this->output_json(200, 'id is incorrect , not exist activity ', [], false);
		}

		return $this->output_json(200, 'find group!', $activity);
	}

	public function getByActivityTypeId(int $activityType): CI_Output
	{
		$activities = $this->ActivityModel->getByActivityTypeId($activityType);

		if (!$activities) {
			return $this->output_json(200, 'id is incorrect , not exist activity ', [], false);
		}

		return $this->output_json(200, 'find activities !', $activities);
	}

	public function insert(): CI_Output
	{
		if (!$this->input->post('nombre')) {
			return $this->output_json(400, 'Debe enviar el nombre');
		}

		if (!$this->input->post('ID_TA')) {
			return $this->output_json(400, 'Debe enviar el ID_TA');
		}

		if (!$this->input->post('puntaje')) {
			return $this->output_json(400, 'Debe enviar el puntaje');
		}

		$inputs = $this->input->post(NULL, TRUE);

		$data = [
			'nombre' => $inputs['nombre'],
			'ID_TA' => $inputs['ID_TA'],
			'puntaje' => $inputs['puntaje']
		];

		$activity = $this->ActivityModel->insert($data);

		if (!$activity) {
			return $this->output_json(400, 'no se pudo insertar el grupo ');
		}

		$activity = $this->ActivityModel->get($this->db->insert_id());

		return $this->output_json(200, 'activity insert', $activity);
	}

	public function update(int $activityId): CI_Output
	{
		$activity = $this->ActivityModel->get($activityId);
		if (!$activity) {
			return $this->output_json(200, 'id is incorrect , not exist activity ', [], false);
		}

		if (!$this->input->post('nombre')) {
			return $this->output_json(400, 'Debe enviar el nombre');
		}

		if (!$this->input->post('ID_TA')) {
			return $this->output_json(400, 'Debe enviar el ID_TA');
		}

		if (!$this->input->post('puntaje')) {
			return $this->output_json(400, 'Debe enviar el puntaje');
		}

		$inputs = $this->input->post(NULL, TRUE);

		$set = [
			'nombre' => $inputs['nombre'],
			'ID_TA' => $inputs['ID_TA'],
			'puntaje' => $inputs['puntaje']
		];

		$activityUpdate = $this->ActivityModel->update($set, ['ID_ACT' => $activityId]);
		if (!$activityUpdate) {
			return $this->output_json(400, 'Error not update activity!');
		}

		return $this->output_json(200, 'activity update !');
	}

	public function delete(int $activityId): CI_Output
	{
		$activity = $this->ActivityModel->get($activityId);
		if (!$activity) {
			return $this->output_json(200, 'id is incorrect , not exist activity ', [], false);
		}

		$resp = $this->ActivityModel->delete($activityId);

		return $resp ? $this->output_json(200, 'delete activity!') : $this->output_json(500, 'have a problem with note deleted!');
	}

	public function insertUser(): CI_Output
	{
		if (!$this->input->post('ID_US')) {
			return $this->output_json(400, 'Debe enviar el ID_US');
		}

		if (!$this->input->post('ID_TA')) {
			return $this->output_json(400, 'Debe enviar el ID_TA');
		}

		if (!$this->input->post('actividades')) {
			return $this->output_json(400, 'Debe enviar actividades');
		}

		$inputs = $this->input->post(NULL, TRUE);

		$activities = json_decode($inputs["actividades"], TRUE);
		$activitiesInsert = [];

		$group = $this->GroupModel->getByUserId($inputs['ID_US']);
		if (!$group) {
			return $this->output_json(400, 'id is incorrect , not exist group ', [], false);
		}

		if (is_array($group)) {
			if (count($group) > 1) {
				return $this->output_json(400, 'user in multi Groups ', [], false);
			}
		}

		$activitiesCount = $this->ActivityModel->getAll(0, 0, ['ID_TA' => $inputs['ID_TA']]);
		if (!$activitiesCount) {
			return $this->output_json(400, 'id is incorrect , not exist activityType ', [], false);
		}

		for ($i = 0; $i < count($activities); $i++):
			$activity = [
				'ID_ACT' => $activities[$i]['ID_ACT'],
				'ID_US' => $inputs['ID_US'],
				'fecha' => date("Y-m-d", strtotime("-1 day")),
				'hora' => date("H:i:s"),
				'ID_GRU' => $group[0]['ID_GRU'],
				'puntaje' => $activities[$i]['puntaje'],
				'puntaje_max' => $activities[$i]['puntajeMax'],
				'actividades' => $activitiesCount
			];

			array_push($activitiesInsert, $activity);
		endfor;

		$activity = $this->ActivityModel->insertUser($activitiesInsert);

		if (!$activity) {
			return $this->output_json(400, 'no se pudo insertar el grupo de actividades ');
		}

		return $this->output_json(200, 'activities insert', $activitiesInsert);
	}

	public function report(): CI_Output
	{
		$params = $this->input->post(['dStart', 'dEnd', 'idGroup'], TRUE);

		$reports = $this->ActivityModel->report($params['idGroup'], $params['dStart'], $params['dEnd']);
		if (!$reports) {
			return $this->output_json(200, 'not reports ', [], false);
		}


		return $this->output_json(200, 'find report !', $reports);
	}
}
