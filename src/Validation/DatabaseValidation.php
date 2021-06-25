<?php namespace SamagTech\Crud\Validation;

use InvalidArgumentException;

class DatabaseValidation {

	//-------------------------------------------------------------------------------------------------------------

	/**
	 * Controllo se esiste l'identificativo 
	 * 
	 */
	public function db_exist($str, string $model) {
		return ! is_null(model(config('Models')->models[$model])->find((int)$str));
	}

	//-------------------------------------------------------------------------------------------------------------

	/**
	 * Controlla la consistenza delle chiavi univoche
	 * 
	 * Ex: db_check_unique_key[MODELLO,CAMPO1,CAMPO2,....,CAMPON]
	 */
	public function db_check_unique_key($str, string $fields, $data) {
		
		$fields = explode(',', $fields);

		// Recupero il modello
		$model = array_shift($fields);

		if ( is_null($model) || count($fields) < 2 ) {
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

		return empty(model(config('Models')->models[$model])->where($where)->findAll());

	}
}
