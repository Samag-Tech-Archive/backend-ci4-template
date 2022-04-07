<?php

namespace SamagTech\Crud\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Migrations;

class CreateMyMigrations extends BaseCommand {

	/**
	 * The Command's Group
	 *
	 * @var string
	 */
	protected $group = 'Development';

	/**
	 * The Command's Name
	 *
	 * @var string
	 */
	protected $name = 'make:mymigration';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'Crea una nuova migrazione';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'make:mymigration [table]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'table'	=> 'Nome della tabella'
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [

	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params) {

		helper('inflector');

		// Recupero il nome del modulo
		$table = array_shift($params);

		// Controllo se il nome è presente, altrimenti lo faccio inserire
		if ( empty($table) ) {
			$table = CLI::prompt("Inserisci il nome del modulo");
		}

		if (empty($table))
		{
			CLI::error('Il nome non è stato inserito');
			return;
		}

		$table = ucfirst($table);

		// Path dove posizionere il file
		$path = APPPATH.'Database/Migrations/';

		$config = new Migrations;

		$filename = date($config->timestampFormat).$table.'.php';


		// Parso il template delle configurazione
		$template = str_replace(['{table}','{table_name}'], [$table, strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $table))], $this->getTemplate());

		write_file($path.$filename, $template);

		CLI::write(CLI::color('Il file di migrazione è stato creato', 'green'));
	}


	//-----------------------------------------------------------------------------------

	/**
	 * Restituisce il template per il modello con l'implemenazione dei file
	 *
	 */
	private function getTemplate() {

		return <<<EOD
		<?php namespace App\Database\Migrations;

		use SamagTech\Crud\Core\MyMigrations;

		class {table} extends MyMigrations {

			protected \$table      = '{table_name}';

			protected bool \$createdDate = true;

			protected bool \$updatedDate = true;

			protected bool \$sysUpdatedDate = true;

			protected bool \$deletedDate = false;

			protected bool \$createdBy = false;

			protected bool \$updatedBy = false;

			public function up() {

				\$fields = [
					'id' => [
						'type'           => 'MEDIUMINT|CHAR',
					],
				];

				\$fields = $this->addFieldsAccessories(\$fields);

				\$this->forge->addField(\$fields);
				\$this->forge->addPrimaryKey('id');
				\$this->forge->createTable(\$this->table, false, ['ENGINE' => 'InnoDB']);
			}

			public function down() {
				\$this->forge->dropTable(\$this->table, true);
			}
		}
		EOD;

	}

	//-----------------------------------------------------------------------------------



}
