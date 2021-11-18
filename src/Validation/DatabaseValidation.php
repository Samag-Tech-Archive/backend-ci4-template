<?php namespace SamagTech\Crud\Validation;

use InvalidArgumentException;

class DatabaseValidation {

	/**
	 * Connessione al database
	 *
	 */
	private $db;

	//-------------------------------------------------------------------------------------------------------------

	/**
	 * Costruttore.
	 *
	 */
	public function __construct() {
		$this->db = \Config\Database::connect();
	}

	//-------------------------------------------------------------------------------------------------------------

	/**
	 * Controllo se esiste l'identificativo
	 *
	 */
	public function db_exist($str, string $table) {
		return ! empty($this->db->table($table)->where('id', (int) $str)->get()->getResultArray());
	}

	//-------------------------------------------------------------------------------------------------------------


	/**
	 * Controllo dell'identificativo opzionale
	 *
	 */
	public function db_exist_not_required($str, string $table) {
		return is_null($str) || ! empty($this->db->table($table)->where('id', (int) $str)->get()->getResultArray());
	}

	//-------------------------------------------------------------------------------------------------------------

	/**
	 * Controlla la consistenza delle chiavi univoche
	 *
	 * Ex: db_check_unique_key[TABELLA,CAMPO1,CAMPO2,....,CAMPON]
	 */
	public function db_check_unique_key($str, string $fields, $data) {

		$fields = explode(',', $fields);

		// Recupero la tabella
		$table = array_shift($fields);

		if ( is_null($table) || count($fields) < 2 ) {
			throw new InvalidArgumentException('Il modello e/o i campi non sono ben impostati.');
		}

		$where = [];

		// Controllo se i campi esistono, se esistono li inserisco nella clausola where
		foreach ( $fields as $field ) {

			if ( ! isset($data[$field]) ) {
				return false;
			}

			$where[$field] = $data[$field];
		}

		return empty($this->db->table($table)->where($where)->get()->getResultArray());

	}

	//-------------------------------------------------------------------------------------------------------------

	/**
	 * Funzione is_unique di CI4 modificata per ignorare i dati cancellati
	 *
	 */
	public function is_unique_with_deleted(string $str = null, string $field, array $data): bool {

		// Recupero i dati
		[$field, $ignoreField, $ignoreValue] = array_pad(explode(',', $field), 3, null);

		sscanf($field, '%[^.].%[^.]', $table, $field);

		$row = $this->db->table($table)
				  ->select('1')
				  ->where($field, $str)
				  ->where('deleted_date', null)
				  ->limit(1);

		if (! empty($ignoreField) && ! empty($ignoreValue) && ! preg_match('/^\{(\w+)\}$/', $ignoreValue)) {
			$row = $row->where("{$ignoreField} !=", $ignoreValue);
		}

		return (bool) ($row->get()->getRow() === null);
	}

	//-------------------------------------------------------------------------------------------------------------

}
