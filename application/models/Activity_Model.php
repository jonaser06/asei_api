<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Activity_Model extends CI_Model
{
	protected $table = 'actividades';

	public function getAll(int $limit = 0, int $offset = 0, array $conditions = [])
	{
		$this->db->where($conditions);
		$countAll = $this->db->count_all_results($this->table, TRUE);
		$this->db->select('act.*, ta.nombre tipo_actividad');
		$this->db->join('tipo_actividad ta', 'ta.ID_TA = act.ID_TA');

		$this->db->from($this->table . ' act');

		if ($limit > 0) {
			$this->db->limit($limit, $offset);
		}

		$activities = $this->db->get()->result_array();

		return $activities ? [
			'countAll' => $countAll,
			'activities' => $activities
		] : FALSE;
	}

	public function get(int $idActivity)
	{
		$this->db->select('ID_ACT, ID_TA, nombre, puntaje');
		$this->db->from($this->table);
		$this->db->where(['ID_ACT' => $idActivity]);
		$activity = $this->db->get()->row_array();

		return $activity ?: FALSE;
	}

	public function getByActivityTypeId(int $activityType)
	{
		$this->db->where(['ID_TA' => $activityType]);

		$countAll = $this->db->count_all_results($this->table, TRUE);

		$this->db->where(['ID_TA' => $activityType]);
		$activities = $this->db->get($this->table)->result_array();

		return $activities ? [
			'countAll' => $countAll,
			'activities' => $activities
		] : FALSE;
	}

	public function insert(array $data = [])
	{
		$activity = $this->db->insert($this->table, $data);
		return $activity ?: FALSE;
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
		$result = $this->db->delete($this->table, ['ID_ACT' => $id]);
		return (bool)$result;
	}

	public function insertUser(array $data = []): bool
	{
		$this->db->trans_start();
		$activity = $this->db->insert_batch('actividades_usuarios', $data);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_commit();
			return TRUE;
		}
	}

	public function getActivityUser(int $limit = 0, int $offset = 0, array $conditions = [])
	{
		$this->db->where($conditions);
		$countAll = $this->db->count_all_results('actividades_usuarios', TRUE);

		$this->db->from('actividades_usuarios');

		if ($limit > 0 && $offset > 0) {
			$this->db->limit($limit, $offset);
		}

		$activities = $this->db->get()->result_array();

		return $activities ? [
			'countAll' => $countAll,
			'activities' => $activities
		] : FALSE;
	}

	public function report($dStart, $dEnd)
	{
		$reportsAux = [];
		$reports = [];

		$sql = "select au.ID_US
       				 , g.nombre                                                            grupo
     				 , ta.ID_TA
					 , fecha
					 , concat(u.NOMBRES, ' ', u.APELLIDO_PATERNO, ' ', u.APELLIDO_MATERNO) participante
					 , au.puntaje_max
					 , a.puntaje
					 , sum(au.puntaje)                                                     puntaje_realizado
					 , (sum(au.puntaje) >= (au.actividades * au.puntaje)) as               pFinal
					 , au.actividades
				from actividades_usuarios au
						 inner join grupos g on g.ID_GRU = au.ID_GRU
						 inner join usuarios u on au.ID_US = u.ID_US
						 inner join actividades a on au.ID_ACT = a.ID_ACT
						 inner join tipo_actividad ta on a.ID_TA = ta.ID_TA
					and fecha >= ?
					and fecha <= ?
				group by au.ID_US, au.fecha, ta.ID_TA
				order by puntaje_realizado desc;";

		$query = $this->db->query($sql, array($dStart, $dEnd));

		foreach ($query->result_array() as $row) {
			if (!array_key_exists($row['ID_US'], $reportsAux)) {
				$reportsAux[$row['ID_US']] = [
					'puntaje_realizado' => 0,
					'puntaje_reconocido' => 0
				];
			}

			$reportsAux[$row['ID_US']]['puntaje_realizado'] += $row['puntaje_realizado'];
			$reportsAux[$row['ID_US']]['puntaje_reconocido'] += ($row['pFinal']) ? $row['puntaje_realizado'] : 0;
			$reportsAux[$row['ID_US']]['grupo'] = $row['grupo'];
			$reportsAux[$row['ID_US']]['usuario'] = $row['participante'];
		}

		$groups = [];
		foreach ($reportsAux as $report) {
			if (!array_key_exists($report['grupo'], $groups)) {
				$groups[$report['grupo']] = 0;
			}

			$groups[$report['grupo']] += $report['puntaje_reconocido'];
		}

		arsort($groups);

		foreach ($reportsAux as $report) {
			$result = [
				'equipo' => $report['grupo'],
				'participante' => $report['usuario'],
				'puntajeEquipo' => $groups[$report['grupo']],
				'puntajeIndividual' => $report['puntaje_reconocido'],
				'ranking' => array_search($groups[$report['grupo']], array_values($groups)) + 1
			];

			array_push($reports, $result);
		}

		$individual_score = array_column($reports, 'puntajeIndividual');

		array_multisort($individual_score, SORT_DESC, $reports);

		return $reports ?: FALSE;
	}
}
