<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Group extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('Group_Model', 'GroupModel');
	}

	public function get()
	{
		$group_quantity = 9;
		$params = $this->input->get(['page', 'limit', 'last', 'search'], TRUE);
		$for_page = $params['limit'] ? (int)$params['limit'] : $group_quantity;
		$offset = $params['page'] ? $for_page * ($params['page'] - 1) : 0;

		$groups = $this->GroupModel->getAll($for_page, $offset, []);

		for ($i = 0; $i < count($groups['groups']); $i++):
			$collaborators = $this->GroupModel->getCollaborator($groups['groups'][$i]['ID_GRU']);
			$groups['groups'][$i]['collaborators'] = (empty($collaborators)) ? [] : $collaborators;
		endfor;

		$page = $params['page'] ? (int)$params['page'] : 1;
		$groups['page'] = $page;
		$pages = ($groups['countAll'] % $for_page) ? (int)($groups['countAll'] / $for_page) + 1 : (int)$groups['countAll'] / $for_page;
		$groups['pages'] = $pages;

		if ($page > 1) {
			$prev = $page - 1;
			$groups['prev'] = "?page=$prev&limit=$for_page";
		}

		if ($page < $pages) {
			$next = $page + 1;
			$groups['next'] = "?page=$next&limit=$for_page";
		}

		$this->output_json(200, 'find groups !', $groups);
	}

	public function getById(int $groupId): CI_Output
	{
		$group = $this->GroupModel->get($groupId);
		if (!$group) {
			return $this->output_json(200, 'id is incorrect , not exist group ', [], false);
		}

		$collaborators = $this->GroupModel->getCollaborator($group['ID_GRU']);
		$group['collaborators'] = (empty($collaborators)) ? [] : $collaborators;

		return $this->output_json(200, 'find group!', $group);
	}

	public function insert(): CI_Output
	{
		$inputs = $this->input->post(NULL, TRUE);

		$data = [
			'nombre' => $inputs['nombre']
		];

		$group = $this->GroupModel->insert($data);

		if (!$group) {
			return $this->output_json(400, 'no se pudo insertar el grupo ');
		}

		$groupId = $this->db->insert_id();

		$group = $this->GroupModel->get($groupId);

		$groupUsers = json_decode($inputs["colaborators"], TRUE);

		if (count($groupUsers) > 0) {
			$groupUsersInsert = [];

			for ($i = 0; $i < count($groupUsers); $i++):
				$user = [
					'ID_GRU' => $groupId,
					'ID_US' => $groupUsers[$i]['ID_US'],
				];

				array_push($groupUsersInsert, $user);
			endfor;


			$users = $this->GroupModel->insertUser($groupId, $groupUsersInsert);

			if (!$users) {
				$this->GroupModel->delete($groupId);
				return $this->output_json(400, 'no se pudo insertar el grupo ');
			}
		}

		return $this->output_json(200, 'group insert', $group);
	}

	public function update(int $groupId): CI_Output
	{
		$group = $this->GroupModel->get($groupId);
		if (!$group) {
			return $this->output_json(200, 'id is incorrect , not exist group ', [], false);
		}

		if (!$this->input->post('nombre')) {
			return $this->output_json(400, 'Debe enviar el nombre');
		}

		$inputs = $this->input->post(NULL, TRUE);

		$set = [
			'nombre' => $inputs['nombre']
		];

		$groupUpdate = $this->GroupModel->update($set, ['ID_GRU' => $groupId]);
		if (!$groupUpdate) {
			return $this->output_json(400, 'Error not update group!');
		}

		$groupUsers = json_decode($inputs["colaborators"], TRUE);

		if (count($groupUsers) > 0) {
			$groupUsersInsert = [];

			for ($i = 0; $i < count($groupUsers); $i++):
				$user = [
					'ID_GRU' => $groupId,
					'ID_US' => $groupUsers[$i]['ID_US'],
				];

				array_push($groupUsersInsert, $user);
			endfor;

			$users = $this->GroupModel->insertUser($groupId, $groupUsersInsert);

			if (!$users) {
				return $this->output_json(400, 'no se pudo insertar el grupo ');
			}
		}

		return $this->output_json(200, 'group update !');
	}

	public function delete(int $groupId): CI_Output
	{
		$group = $this->GroupModel->get($groupId);
		if (!$group) {
			return $this->output_json(200, 'id is incorrect , not exist group ', [], false);
		}

		$resp = $this->GroupModel->delete($groupId);

		return $resp ? $this->output_json(200, 'delete group!') : $this->output_json(500, 'have a problem with note deleted!');
	}
}
