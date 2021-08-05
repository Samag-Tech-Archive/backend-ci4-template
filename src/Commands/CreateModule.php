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
	protected $usage = 'make:module [module_name] [options]';

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
	protected $options = [
		'--file' 		=> 'Modulo con gestione di file',
		// '--only-file' 	=> 'Modulo con di file'
	];

	/**
	 * Actually execute a command.
	 *
	 * @param array $params
	 */
	public function run(array $params) {

		helper('inflector');

		// Recupero il nome del modulo
		$moduleName = array_shift($params);

		// if ( CLI::getOption('file') && CLI::getOption('only-file') ) {
		// 	CLI::write('Le opzioni --file e --only-file non possono essere usate contemporaneamente', 'red');
		// 	return;
		// }

		// Implementazione gestione di 
		$file = CLI::getOption('file') ?? null;

		if ( $file && ! is_string($file) ) {
			$file = CLI::prompt('Inserisci il nome del modello del file');
		}

		$useFile = $file && ! is_null($file) && is_string($file);

		// $onlyFile = CLI::getOption('only-file') ?? null;

		// if ( $onlyFile && ! is_string($onlyFile) ) {
		// 	$onlyFile = CLI::prompt('Inserisci il nome del modello del file');
		// }

		// $useOnlyFile = $onlyFile && ! is_null($onlyFile) && is_string($onlyFile);

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
		$template = str_replace(['{route}','{moduleName}'], [ lcfirst($moduleName), $moduleName] , $useFile ? $this->getConfigWithFileTemplate() : $this->getConfigTemplate());

		write_file($configModulePath.'/Routes.php', $template);

		// Parso il template per il controller
		$template = str_replace('{moduleName}', $moduleName, $useFile ? $this->getControllerWithFileTemplate() : $this->getControllerTemplate());

		write_file($controllerModulePath.'/'.$moduleName.'.php', $template);

		$modelName = singular($moduleName);

		// Parso il template per il servizio
		if ( $useFile ) {
			$template = str_replace(['{moduleName}', '{modelName}', '{fileModelName}'], [$moduleName, $modelName, $file], $this->getServiceWithFileTemplate());
		} 
		else {
			$template = str_replace(['{moduleName}', '{modelName}'], [$moduleName, $modelName], $this->getServiceTemplate());
		}

		write_file($serviceModulePath.'/'.$moduleName.'.php', $template);

		$table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $moduleName));

		$table_split = explode('_', $table);

		$alias = '';

		foreach ( $table_split as $split ) {
			$alias .= substr(ucfirst($split),0,1); 
		}

		// Parso il template per il modello
		$template = str_replace(['{moduleName}', '{modelName}', '{table}', '{alias}'], [$moduleName,$modelName,$table,$alias], $this->getModelTemplate());

		write_file($modelModulePath.'/'.$modelName.'Model.php', $template);

		if ( $useFile ) {

			$table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', plural($file)));

			$table_split = explode('_', $table);

			$alias = '';

			foreach ( $table_split as $split ) {
				$alias .= substr(ucfirst($split),0,1); 
			}

			// Parso il template per il modello
			$template = str_replace(['{moduleName}', '{fileModelName}', '{table}', '{alias}'], [$moduleName,$file,$table,$alias], $this->getModelWithFileTemplate());

			write_file($modelModulePath.'/'.$file.'Model.php', $template);
		}

		CLI::write(CLI::color('Il modulo è stato creato', 'green'));
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
	 * Restituisce il template del file di config
	 * 
	 */
	private function getConfigWithFileTemplate() {

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
			\$subroutes->post('files', '{moduleName}::uploads');
			\$subroutes->post('files/(:num)', '{moduleName}::uploads/$1');
			\$subroutes->get('files/(:num)', '{moduleName}::download/$1');
			\$subroutes->delete('files/(:num)', '{moduleName}::deleteFile/$1');
			\$subroutes->get('files/(:num)/resource', '{moduleName}::downloadAllByResource/$1');
			\$subroutes->get('files', '{moduleName}::downloadFiles');

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
	 * Restituisce il template del controller
	 * 
	 */
	private function getControllerWithFileTemplate() {

		return <<<EOD
		<?php namespace App\Modules\{moduleName}\Controllers;

		use SamagTech\Crud\Core\FileServiceController;
		
		class {moduleName} extends FileServiceController {}
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
	private function getServiceWithFileTemplate() {
		
		return <<<EOD
		<?php namespace App\Modules\{moduleName}\Services;

		use SamagTech\Crud\Core\CRUDFileService;
		use App\Modules\{moduleName}\Models\{modelName}Model;
		use App\Modules\{moduleName}\Models\{fileModelName}Model;
		
		class {moduleName} extends CRUDFileService {

			protected ?string \$modelName = {modelName}Model::class;

			protected ?string \$fileModelName = {fileModelName}Model::class;

			protected array \$validationsRules = [
				'generic'	=> [],
				'insert'	=> [],
				'update'	=> [],
			];

			protected array \$validationsCustomMessage = [];
			
			protected array \$validationsUploadsRules = [];

			protected array \$validationsUploadsCustomMessage = [];
			
			public function __construct() {
				parent::__construct();
			}
		}
		EOD;
	}

    //-----------------------------------------------------------------------------------
	
	/**
	 * Restituisce il template per il modello
	 * 
	 */
	private function getModelTemplate() {
		
		return <<<EOD
		<?php namespace App\Modules\{moduleName}\Models;

		use SamagTech\Crud\Core\CRUDModel;

		class {modelName}Model extends CRUDModel {
			
			protected \$table      = '{table}';
			protected \$alias      = '{alias}';
		}
		EOD;

	}

	//-----------------------------------------------------------------------------------
	
	/**
	 * Restituisce il template per il modello con l'implemenazione dei file
	 * 
	 */
	private function getModelWithFileTemplate() {
		
		return <<<EOD
		<?php namespace App\Modules\{moduleName}\Models;

		use SamagTech\Crud\Core\CRUDModel;
		use SamagTech\Crud\Core\FileModelInterface;

		class {fileModelName}Model extends CRUDModel implements FileModelInterface {
			
			protected \$table      = '{table}';
			protected \$alias      = '{alias}';


			public function getFileByID(int \$fileID) : ?array {
				// Code ...
				return null;
			}

			public function getFilesByResource(int \$resourceID) : ?array {
				// Code...
				return null;
			}
		}
		EOD;

	}

    //-----------------------------------------------------------------------------------


	
}
