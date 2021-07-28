<?php

namespace SamagTech\Crud\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateModule extends BaseCommand {
	
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
	protected $name = 'make:module';

	/**
	 * The Command's Description
	 *
	 * @var string
	 */
	protected $description = 'Crea un nuovo modulo';

	/**
	 * The Command's Usage
	 *
	 * @var string
	 */
	protected $usage = 'make:module [module_name]';

	/**
	 * The Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'module_name'	=> 'Nome del modulo'
	];

	/**
	 * The Command's Options
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params) {

		helper('inflector');

		// Recupero il nome del modulo
		$moduleName = array_shift($params);

		// Controllo se il nome è presente, altrimenti lo faccio inserire
		if ( empty($moduleName) ) {
			$moduleName = CLI::prompt("Inserisci il nome del modulo");
		}

		if (empty($moduleName))
		{
			CLI::error('Il nome non è stato inserito');
			return;
		}

		// Imposto il path di default
		$modulePath = APPPATH.'Modules/';

		// Creo i path principali
		$configModulePath = $modulePath.$moduleName.'/Config';
		$controllerModulePath = $modulePath.$moduleName.'/Controllers';
		$serviceModulePath = $modulePath.$moduleName.'/Services';
		$modelModulePath = $modulePath.$moduleName.'/Models';

		// Creo le cartelle
		mkdir($modulePath.$moduleName);
		mkdir($configModulePath);
		mkdir($controllerModulePath);
		mkdir($serviceModulePath);
		mkdir($modelModulePath);

		// Parso il template delle configurazione
		$template = str_replace(['{route}','{moduleName}'], [ lcfirst($moduleName), $moduleName] , $this->getConfigTemplate());

		write_file($configModulePath.'/Routes.php', $template);

		// Parso il template per il controller
		$template = str_replace('{moduleName}', $moduleName, $this->getControllerTemplate());

		write_file($controllerModulePath.'/'.$moduleName.'.php', $template);

		$modelName = singular($moduleName);

		// Parso il template per il servizio
		$template = str_replace(['{moduleName}', '{modelName}'], [$moduleName, $modelName], $this->getServiceTemplate());

		write_file($serviceModulePath.'/'.$moduleName.'.php', $template);

		$table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $moduleName));

		$table_split = explode('_', $table);

		$alias = '';

		foreach ( $table_split as $split ) {
			die(substr(ucfirst($split), 1)); 
		}

		// Parso il template per il modello
		$template = str_replace(['{moduleName}', '{modelName}', '{table}', '{alias}'], [$moduleName,$modelName,$table,$alias], $this->getModelTemplate());

		write_file($modelModulePath.'/'.$modelName.'Model.php', $template);

		CLI::write(CLI::color('Il module è stato creato', 'green'));
	}	

	//-----------------------------------------------------------------------------------
	
	/**
	 * Restituisce il template del file di config
	 * 
	 */
	private function getConfigTemplate() {

		return <<<EOD
		<?php 
		
		if ( !isset(\$routes) ) {
			\$routes = \Config\Services::routes(true);
		}

		\$routes->setDefaultNamespace('App\Modules\{moduleName}\Controllers');
		
		\$routes->group('{route}', function(\$subroutes) {

			\$subroutes->get('/', '{moduleName}::retrieve');
			\$subroutes->get('(:num)', '{moduleName}::retrieve/$1');
			\$subroutes->post('/', '{moduleName}::create');
			\$subroutes->put('(:num)', '{moduleName}::update/$1');
			\$subroutes->delete('(:num)', '{moduleName}::delete/$1');

		});
		EOD;
	}

	//-----------------------------------------------------------------------------------

	/**
	 * Restituisce il template del controller
	 * 
	 */
	private function getControllerTemplate() {

		return <<<EOD
		<?php namespace App\Modules\{moduleName}\Controllers;

		use SamagTech\Crud\Core\ServiceController;
		
		class {moduleName} extends ServiceController {}
		EOD;		
	}

	//-----------------------------------------------------------------------------------
	
	/**
	 * Restituisce il template per il servizio
	 * 
	 */
	private function getServiceTemplate() {
		
		return <<<EOD
		<?php namespace App\Modules\{moduleName}\Services;

		use SamagTech\Crud\Core\CRUDService;
		use App\Modules\{moduleName}\Models\{modelName}Model;
		
		class {moduleName} extends CRUDService {

			protected ?string \$modelName = {modelName}Model::class;

			protected array \$validationsRules = [
				'generic'	=> [],
				'insert'	=> [],
				'update'	=> [],
			];

			protected array \$validationsCustomMessage = [];
			
			
			public function __construct() {
				parent::__construct();
			}
		}
		EOD;
	}

    //-----------------------------------------------------------------------------------
	
	/**
	 * Restituisce il template per il servizio
	 * 
	 */
	private function getModelTemplate() {
		
		return <<<EOD
		<?php namespace App\Modules\{moduleName}\Models;

		use SamagTech\Crud\Core\CRUDModel;

		class {modelName}Model extends CRUDModel {
			
			protected \$table      = '{table}';
			protected \$alias      = '';
		}
		EOD;

	}

    //-----------------------------------------------------------------------------------


	
}
