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
		'--bulk'		=> 'Modulo con le funzionalità bulk',
		'--mvc'			=> 'Utilizza i namespace dell\'mvc'
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

		// Implementazione gestione di
		$file = CLI::getOption('file') ?? null;

		$mvc = CLI::getOption('mvc');

		if ( $file && ! is_string($file) ) {
			$file = CLI::prompt('Inserisci il nome del modello del file');
		}

		// Implementazione delle funzionalità bulk
		$useBulk = CLI::getOption('bulk');

		$useFile = $file && ! is_null($file) && is_string($file);

		if ( $useFile && $useBulk ) {
			CLI::error('L\'opzione file e bulk non possono essere eseguite insieme');
			return;
		}

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
		$modulePath = $mvc ? APPPATH : APPPATH.'Modules/' ;

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
		if ( is_null($mvc) ) {

			$template = str_replace(['{route}','{moduleName}'], [ lcfirst($moduleName), $moduleName] , $useFile ? $this->getConfigWithFileTemplate() : $this->getConfigTemplate($useBulk));

			write_file($configModulePath.'/Routes.php', $template);
		}

		// Parso il template per il controller
		$template = str_replace('{moduleName}', $moduleName, $useFile ? $this->getControllerWithFileTemplate($mvc) : $this->getControllerTemplate($useBulk, $mvc));

		write_file($controllerModulePath.'/'.$moduleName.'.php', $template);

		$modelName = singular($moduleName);

		$table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $moduleName));

		// Parso il template per il servizio
		if ( $useFile ) {
			$template = str_replace(['{moduleName}', '{modelName}', '{fileModelName}'], [$moduleName, $modelName, $file], $this->getServiceWithFileTemplate($mvc));
		}
		else {
			$template = str_replace(['{moduleName}', '{modelName}', '{table}'], [$moduleName, $modelName, $table], $this->getServiceTemplate($useBulk, $mvc));
		}

		write_file($serviceModulePath.'/'.$moduleName.'.php', $template);


		$table_split = explode('_', $table);

		$alias = '';

		foreach ( $table_split as $split ) {
			$alias .= substr(ucfirst($split),0,1);
		}

		// Parso il template per il modello
		$template = str_replace(['{moduleName}', '{modelName}', '{table}', '{alias}'], [$moduleName,$modelName,$table,$alias], $this->getModelTemplate($mvc));

		write_file($modelModulePath.'/'.$modelName.'Model.php', $template);

		if ( $useFile ) {

			$table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', plural($file)));

			$table_split = explode('_', $table);

			$alias = '';

			foreach ( $table_split as $split ) {
				$alias .= substr(ucfirst($split),0,1);
			}

			// Parso il template per il modello
			$template = str_replace(['{moduleName}', '{fileModelName}', '{table}', '{alias}'], [$moduleName,$file,$table,$alias], $this->getModelWithFileTemplate($mvc));

			write_file($modelModulePath.'/'.$file.'Model.php', $template);
		}

		CLI::write(CLI::color('Il modulo è stato creato', 'green'));
	}

	//-----------------------------------------------------------------------------------

	/**
	 * Restituisce il template del file di config
	 *
	 */
	private function getConfigTemplate($useBulk = false) {

		$bulkTemplate = '';

		if ( $useBulk ) {
			$bulkTemplate = <<<EOD
			\$subroutes->post('bulk', '{moduleName}::bulkCreate');
				\$subroutes->put('bulk', '{moduleName}::bulkUpdate');
				\$subroutes->delete('bulk', '{moduleName}::bulkDelete');
			EOD;
		}

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
			\$subroutes->get('/', '{moduleName}::export');
			$bulkTemplate
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
			\$subroutes->get('/', '{moduleName}::export');
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
	private function getControllerTemplate($useBulk = false, $mvc = false) {

		$controllerName = 'ServiceController';

		if ( $useBulk ) {
			$controllerName = 'BulkServiceController';
		}

		$namespace = 'App\Modules\{moduleName}\Controllers';

		if ( $mvc ) {
			$namespace = 'App\Controllers';
		}

		return <<<EOD
		<?php namespace $namespace

		use SamagTech\Crud\Core\\$controllerName;
		use App\Modules\{moduleName}\Services\{moduleName} as Services{moduleName};

		class {moduleName} extends $controllerName {

			protected ?string \$defaultService = Services{moduleName}::class;
		}
		EOD;
	}

	//-----------------------------------------------------------------------------------

	/**
	 * Restituisce il template del controller
	 *
	 */
	private function getControllerWithFileTemplate($mvc = false) {

		$namespace = 'App\Modules\{moduleName}\Controllers';

		if ( $mvc ) {
			$namespace = 'App\Controllers';
		}

		return <<<EOD
		<?php namespace $namespace;

		use SamagTech\Crud\Core\FileServiceController;
		use App\Modules\{moduleName}\Services\{moduleName} as Services{moduleName};

		class {moduleName} extends FileServiceController {

			protected ?string \$defaultService = Services{moduleName}::class;
		}
		EOD;
	}

	//-----------------------------------------------------------------------------------

	/**
	 * Restituisce il template per il servizio
	 *
	 */
	private function getServiceTemplate($useBulk = false, $mvc = false) {

		$serviceName = 'CRUDService';
		$keyForBulk = '';

		if ( $useBulk ) {
			$serviceName = 'CRUDBulkService';
			$keyForBulk = 'protected ?string $keyBulk = \'{table}\';';
		}

		$namespace = 'App\Modules\{moduleName}\Services';

		if ( $mvc ) {
			$namespace = 'App\Services';
		}

		return <<<EOD
		<?php namespace $namespace;

		use SamagTech\Crud\Core\\$serviceName;
		use App\Modules\{moduleName}\Models\{modelName}Model;

		class {moduleName} extends $serviceName {

			protected ?string \$modelName = {modelName}Model::class;

			$keyForBulk

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
	private function getServiceWithFileTemplate($mvc = false) {

		$namespace = 'App\Modules\{moduleName}\Services';

		if ( $mvc ) {
			$namespace = 'App\Services';
		}

		return <<<EOD
		<?php namespace $namespace;

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

			public function createUploadRow(UploadedFile \$file, string \$hashName, ?array \$resource = null ) : array {
				return [];
			}

			public function getDownloadData(array \$file) : array {
				return [];
			}
		}
		EOD;
	}

    //-----------------------------------------------------------------------------------

	/**
	 * Restituisce il template per il modello
	 *
	 */
	private function getModelTemplate($mvc = false) {

		$namespace = 'App\Modules\{moduleName}\Models';

		if ( $mvc ) {
			$namespace = 'App\Models';
		}

		return <<<EOD
		<?php namespace $namespace;

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
	private function getModelWithFileTemplate($mvc = false) {

		$namespace = 'App\Modules\{moduleName}\Models';

		if ( $mvc ) {
			$namespace = 'App\Models';
		}

		return <<<EOD
		<?php namespace $namespace;

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
