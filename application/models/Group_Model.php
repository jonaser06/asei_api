<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Group_Model extends CI_Model
{
	protected $table = 'grupos';

	public function getAll(int $limit = 1, int $offset = 0, array $conditions = [])
	{
		$this->db->where($conditions);

		$countAll = $this->db->count_all_results($this->table, FALSE);
		$this->db->limit($limit, $offset);

		$groups = $this->db->get()->result_array();

		return $groups ? [
			'countAll' => $countAll,
			'groups' => $groups
		] : FALSE;
	}

	public function get(int $idGroup)
	{
		$this->db->select('ID_GRU, nombre');
		$this->db->from($this->table);
		$this->db->where(['ID_GRU' => $idGroup]);
		$group = $this->db->get()->row_array();

		return $group ?: FALSE;
	}

	public function insert(array $data = [])
	{
		$group = $this->db->insert($this->table, $data);
		return $group ?: FALSE;
	}

	public function update(array $set, array $where): bool
	{
		if (empty($set)) {
			return false;
		}

		$this->db->set($set);
		$this->db->where($where);
		return (bool)$this->db->update($this->table);
	}

	public function delete(int $id): bool
	{
		$result = $this->db->delete($this->table, ['ID_GRU' => $id]);
		return (bool)$result;
	}

	public function getCollaborator(int $idGroup)
	{
		$this->db->select('*');
		$this->db->from('grupos_usuarios');
		$this->db->join('usuarios as u', 'u.ID_US =grupos_usuarios.ID_US');
		$this->db->where(['ID_GRU' => $idGroup]);
		$xx = $this->db->get()->result_array();
		return $xx ? $xx : FALSE;
	}

	public function insertUser(int $id, array $data = []): bool
	{
		$this->db->trans_start();

		$this->db->where('ID_GRU', $id);
		$this->db->delete('grupos_usuarios');

		$group = $this->db->insert_batch('grupos_usuarios', $data);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_commit();
			return TRUE;
		}
	}

	public function getByUserId(int $id)
	{
		$this->db->select('*');
		$this->db->from('grupos_usuarios');
		$this->db->where(['ID_US' => $id]);
		$group = $this->db->get()->result_array();
		return $group ?: FALSE;
	}
}
