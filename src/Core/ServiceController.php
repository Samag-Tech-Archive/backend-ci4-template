<?php namespace SamagTech\Crud\Core;

use CodeIgniter\Controller as Controller;
use \CodeIgniter\HTTP\Response as Response;
use CodeIgniter\API\ResponseTrait;
use SamagTech\Crud\Exceptions\CreateException;
use SamagTech\Crud\Exceptions\DeleteException;
use SamagTech\Crud\Exceptions\GenericException;
use SamagTech\Crud\Exceptions\ResourceNotFoundException;
use SamagTech\Crud\Exceptions\UpdateException;
use SamagTech\Crud\Exceptions\ValidationException;
use SamagTech\Crud\Singleton\CurrentUser;
use SamagTech\Crud\Traits\CrudTrait;

/**
 * Classe astratta per la definizione di un nuovo CRUD.
 *
 * @implements CrudInterface
 * @extends Controller
 * @abstract 
 */
abstract class ServiceController extends Controller implements ServiceControllerInterface {

    use ResponseTrait, CrudTrait;

    /**
     * Variabile per la definizione del sottomodulo che deve essere utilizzato
     * 
     * @var CRUDService
     * @access public
     */
    public CRUDService $service;

    /**
     * Variabile che contiene i dati inerenti all'utente 
     * autenticato tramite JWT
     * 
     * @var CurrentUser
     * @access public
     */
    public ?object $currentUser = null;

    /**
     * Array contenente i messaggi di default 
     * per le risposte delle API
     * 
     * @var array
     * @access public 
     */
    public array $messages = [
        'create'        =>  'La risorsa è stata creata',
        'retrieve'      =>  'Lista risorse',
        'update'        =>  'La risorsa è stata modificata',
        'delete'        =>  'La risorsa è stata cancellata',
    ];


    /**
     * Service di default
     * 
     * @var string
     * @access protected
     */
    protected string $defaultService;

    /**
     * Lista di servizi esterni al default service
     * 
     * Es. [
     *  'token1' => 'Servizio1',
     *  'token2' => 'Servizio2',
     * ]
     * 
     * @var string[]
     * @access protected
     */
    protected ?array $services = null;

    /**
	* An array of helpers to be loaded automatically upon
	* class instantiation. These helpers will be available
	* to all other controllers that extend BaseController.
	*
	* @var array
	*/
	protected $helpers = [];

	/**
	 * Constructor.
	 */
	public function initController(
        \CodeIgniter\HTTP\RequestInterface $request, 
        \CodeIgniter\HTTP\ResponseInterface $response, 
        \Psr\Log\LoggerInterface $logger
    ) {
    
        // Do Not Edit This Line
		parent::initController($request, $response, $logger);

		//--------------------------------------------------------------------
		// Preload any models, libraries, etc, here.
		//--------------------------------------------------------------------
		// E.g.:
        // $this->session = \Config\Services::session();
    }

    //--------------------------------------------------------------------------------------------

    /**
     * Costruttore.
     * 
     */
    public function __construct() {

        // Recupero i dati dell'utente autenticato
        $this->currentUser = CurrentUser::getIstance()->getProperty();

        // Servizio di default
        $className = $this->getCalledClassName();
        $this->defaultService = "App\Modules\\$className\Services\\$className";
        
        // Inizializzo il servizio da utilizzare
        $this->service = $this->getFactory($this->currentUser->app_token ?? null);

    }

    //--------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     * 
     * @implements Factory
     * 
     */
    public function getFactory($token): CRUDService {

        if ( ! is_null($token) && ! is_null($this->services) && isset($this->services[$token]) ) {

            $calledClass = $this->getCalledClassName();
            $nameService = $this->services[$token];

            $service = "App\Modules\\$calledClass\Services\\$nameService";

            return new $service;
        }
        
        return new $this->defaultService;
    }

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la creazione di una nuova risorsa
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function create() : Response {

        $resourceId = 0;

        // Recupero l'identificativo della risorsa appena creata
        try {
            $resourceId = $this->service->create($this->request);
        }
        catch(ValidationException $e) {
            return $this->failValidationErrors($e->getValidationErrors());
        }
        catch(CreateException | GenericException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->respondCreated(['item_id'  =>  $resourceId]);
        
    }

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la lettura dei dati di una o più risorse
     * 
     * @param int $id   Identificativo della singola risorsa ( Default 'null')
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function retrieve(int $id = null) : Response {

        try {
            $data = $this->service->retrieve($this->request, $id);
        }
        catch(ResourceNotFoundException $e ) {
            return $this->failNotFound($e->getMessage());
        }
        catch(GenericException $e) {
            return $this->fail($e->getMessage());
        }
        
        return $this->respond($data, 200, $this->messages['retrieve']);
    }

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la modifica di una risorsa
     * 
     * @param int $id   Identificativo della risorsa da modifica
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function update(int $id ) : Response {

        // Recupero l'identificativo della risorsa appena creata
        try {
            $this->service->update($this->request,$id);
        }
        catch(ValidationException $e) {
            return $this->failValidationErrors($e->getValidationErrors());
        }
        catch ( ResourceNotFoundException $e ) {
            return $this->failNotFound($e->getMessage());
        }
        catch(UpdateException | GenericException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->respondUpdated(['item_id' => $id], $this->messages['update']);
    }

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la cancellazione di una risorsa
     *  
     * @param int $id  Identificativo della risorsa da cancellare
     * 
     * @return \CodeIgniter\HTTP\Response
     */
    public function delete(int $id) : Response {
        
        // Recupero l'identificativo della risorsa appena creata
        try {
            $this->service->delete($this->request,$id);
        }
        catch ( ResourceNotFoundException $e ) {
            return $this->failNotFound($e->getMessage());
        }
        catch(DeleteException | GenericException $e) {
            return $this->fail($e->getMessage());
        }

        return $this->respondDeleted(['item_id'  =>  $id], $this->messages['delete']);
    }
    
    //-----------------------------------------------------------------------------

    /**
     * Funzione per il fixing del CORS 
     * 
     * @return Response 
     */
    public function  options() : Response {
        return $this->response->setStatusCode(Response::HTTP_OK);
    }
}