<?php namespace SamagTech\Crud\Core;

use CodeIgniter\HTTP\IncomingRequest;
use SamagTech\Crud\Exceptions\ValidationException;
use SamagTech\Crud\Exceptions\CreateException;
use SamagTech\Crud\Exceptions\UpdateException;
use SamagTech\Crud\Exceptions\DeleteException;
use SamagTech\Crud\Exceptions\ResourceNotFoundException;
use SamagTech\Crud\Singleton\CurrentUser;
use CodeIgniter\I18n\Time;
use SamagTech\Crud\Config\Application;
use SamagTech\Log\Libraries\Log;

/**
 * Classe astratta che implementa i servizi CRUD di default
 * 
 * @implements Service
 * @author Alessandro Marotta
 */
abstract class CRUDService implements Service {

    /**
     * Array con gli helpers da precaricare
     * 
     * 
     * @var array
     * @access protected
     */
    protected array $helpers = [
        'debug',
        'text',
        'array'
    ];

    /**
     * Array contenente le regole di validazioni.
     * 
     * L'array deve contenere 3 chiavi principali : 'generic', 'insert', 'update'
     * 
     * 'generic' → Verrà utilizzata sia in create che update
     * 'insert' → Verrà utilizzata solo in create
     * 'update' → Verrà utilizzata solo in update
     * 
     * @var array 
     * @access protected 
     */
    protected array $validationsRules = [];

    /**
     * Array contenente i messaggi di validazioni custom
     * 
     * L'array deve contenere TIPO_VALIDAZIONE -> MESSAGGIO
     * 
     * @var array
     * @access protected
     */
    protected array $validationsCustomMessage = [];

    /**
     * Stringa contentente il nome del modello.
     * 
     * @var string
     * @access protected
     * Default ''
     */
    protected ?string $modelName = null;
    
    /**
     * Modello principale
     * 
     * @var CRUDModel
     * @access protected
     */
    protected ?CRUDModel $model = null;

    /**
     * Lista contentente la clausola per la select
     * 
     * @var array
     * @access protected
     * Default []
     */
    protected array $retrieveSelect = [];

    /**
     * Lista contenente le clausole per i join
     * 
     * @var array
     * @access protected
     * Default null
     */
    protected ?array $retrieveJoin = null;

    /**
     * Lista contenente le clausole where di base
     * 
     * @var array
     * @access protected
     * Default null
     */
    protected ?array $retrieveWhere = null;

    /**
     * Lista contenente le clausole group by di base
     * 
     * @var array 
     * @access protected
     * Default null
     */
    protected ?array $retrieveGroupBy = null;

    /**
     * Limite di risorse da recuperare per pagina.
     * 
     * Se è settato il flag noPagination a true non viene considerato.
     * 
     * @var int
     * @access protected
     * Default 50
     */
    protected int $retriveLimit = 25;

    /**
     * Flag per attivare/disabilitare la paginazione. ( Utile principalmente per i report)
     * 
     * @var bool
     * @access protected
     * Default FALSE
     */
    protected bool $noPagination = false;

    /**
     * Clausola per l'ordinamento di default
     * 
     * @var array
     * @access protected
     * Default 'id'
     */
    protected array $retrieveSortBy = ['id:desc'];

    /**
     * Mappa che deve contentenere la codifica dei campi che possono servire
     * per ordinamento e filtri nelle tabelle joinate
     * 
     * L'array deve essere formato con  FILTRO → CAMPO_CON_ALIAS
     * 
     * Es. employee_name => E.name dove E è l'alias della tabella 
     * 
     * @var array 
     * @access protected
     * Default []
     */
    protected array $joinFieldsFilters = [];

    /**
     * Mappa che deve contentenere la codifica dei campi che possono servire
     * per ordinamento nelle tabelle joinate
     * 
     * L'array deve essere formato con  ORDINAMENTO → CAMPO_CON_ALIAS
     * 
     * Es. employee_name => E.name dove E è l'alias della tabella 
     * 
     * @var array 
     * @access protected
     * Default []
     */
    protected array $joinFieldsSort = [];

    /**
     * Istanza del database 
     * 
     * @var BaseConnection 
     */
    protected $db; 

    /**
     * Configurazione Applicazione
     * 
     * @var Application
     * @access protected
     */
    protected Application $appConfig;

    /**
     * Dati utente loggato
     * 
     * @var object
     * @access protected 
     */
    protected ?object $currentUser;

    /**
     * Libreria per il logger
     * 
     * @var Log
     * @access protected 
     */
    protected Log $logger;
    
    /**
     * Flag per l'attivazione del logger
     * nel singolo CRUD
     * 
     * @var bool
     * @access protected 
     */
    protected bool $activeCrudLogger = true;
    

    //---------------------------------------------------------------------------------

    /**
     *  Costruttore. 
     * 
     */
    public function __construct() {

        // Carico tutti gli helpers necessari
        helper($this->helpers);

        $this->appConfig = config('Application');

        // Controllo se validation sia una array
        if ( ! is_array($this->validationsRules) ) {
            die ('Le regole di validazione devono essere contenute in un array');
        }
        
        // Se il modello non è settato allora setto il modello di default
        if ( is_null($this->modelName) ) {
            die('Il modello non è settato');
        } 
        else {
            $this->model = model($this->modelName);
        }

        // Istanzio la classe del database per usare le transazioni
        $this->db = \Config\Database::connect();

        // Setto i dati dell'utente loggato
        $this->currentUser = CurrentUser::getIstance()->getProperty();

        // Inizializzo la libreria di log
        $this->logger = new Log($this->model->getTable(), $this, CurrentUser::getIstance() ?? null);
        
    }

    //---------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     * 
     */
    public function create(IncomingRequest $request) : array {

        // Eseguo il check della validazione
        $this->checkValidation($request,'insert');

        // Recupero i dati dalla richiesta
        $data = $request->getJSON(TRUE);

        // Callback pre-inserimento
        $data = $this->preInsertCallback($data);

        // Callback per estrarre dati esterni alla riga
        $extraInsert = $this->getExtraData($data);

        // Inizializzo la transazione
        $this->db->transStart();

        // Se è impostato la colonna created_by aggiungo l'utente corrente
        if ( $this->model->useCreatedBy ) {
            $data = array_merge($data, [
                'created_by' => $this->currentUser->id,
            ]);
        } 

        // Inserisco i dati
        $id = $this->model->insert($data);

        $this->logger->create('create', $id, $data);

        // Se esistono dati extra allora eseguo la callback per gestirli
        $this->insertCallback($extraInsert);
        
        // Callback post-inserimento
        $this->postInsertCallback($id,$data,$extraInsert);

        // Termino la transazione
        $this->db->transComplete();

        // Se la transazione è fallita sollevo un eccezione
        if ( $this->db->transStatus() === FALSE ) {
            throw new CreateException();
        }

        return $this->model->find($id);
    }

    //--------------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     * 
     */
    public function retrieve( IncomingRequest $request, int $id = null) : array {

        // Controllo se la risorsa esiste
        if  ( ! is_null($id) && ! $this->model->exist($id) ) {
            throw new ResourceNotFoundException();
        }

        // Recupero i parametri da eseguire 
        $params = $request->getGet();

        // Creo l'array con la configurazione per la query
        $options = [
            'select'    =>  $this->retrieveSelect,
            'where'     =>  $this->retrieveWhere,
            'join'      =>  $this->retrieveJoin,
            'group_by'  =>  $this->retrieveGroupBy,
            'limit'     =>  ! isset($params['for_page']) ? $this->retriveLimit : $params['for_page'],
            'offset'    =>  ! isset($params['page']) ? 1 : $params['page'], 
            'sort_by'   =>  ! isset($params['sort_by']) ? $this->retrieveSortBy : [$params['sort_by']], 
            'no_pagination' => $this->noPagination,
            'item_id'   =>  $id
        ];

        // Elimino dai parametri i dati già recuperati
        unset($params['sort_by'], $params['page'], $params['for_page']);

        // Risposta per la lista
        $data = [
            'page'          => $options['offset'],
            'total_rows'    => 0,
            'rows'          => [] 
        ];

        // Aggiunge dati alla query e modifica le opzioni
        $options = $this->preRetrieveCallback($options, $params);

        // Recupera i dati in base ai parametri
        $data['rows'] = $this->model->getWithParams($options, $params, $this->joinFieldsFilters, $this->joinFieldsSort);

        // Restutuisce tutte le righe trovate non paginate
        $data['total_rows'] = (int)$this->model->getFoundRows();
        
        // Se è impostato l'identificativo setto data con i dati di rows
        if ( ! is_null($id) ) {
            $data = $data['rows'];
        }

        // Callback per la modifica della lista
        $data = $this->retrieveCallback($data, ! is_null($id));

        // Callback per la modifica della lista
        $data = $this->postRetrieveCallback($data, ! is_null($id));

        return $data;
    }

    //--------------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     * 
     */
    public function update(IncomingRequest $request, int $id) : bool {

        // Controllo se la risorsa esiste
        if  ( is_null($oldData = $this->model->find($id)) ) {
            throw new ResourceNotFoundException();
        }

        // Eseguo il check della validazione
        $this->checkValidation($request,'update');

        // Recupero i dati dalla richiesta
        $data = $request->getJSON(TRUE);
        
        // Callback pre-modifica
        $data = $this->preUpdateCallback($id, $data);

        // Callback per estrarre dati esterni alla riga
        $extraUpdate = $this->getExtraData($data);

        // Inizializzo la transazione
        $this->db->transStart();

        // Se è impostato la colonna updated_by aggiungo l'utente corrente
        if ( $this->model->useUpdatedBy ) {
            $data = array_merge($data, [
                'updated_by' => $this->currentUser->id,
            ]);
        } 

        $data['updated_date'] = Time::now();

        // Inserisco i dati
        $isUpdate = $this->model->update($id,$data);

        // Check per il logger
        $this->logger->create('update', $id, $oldData, $data);

        // Se esistono dati extra allora eseguo la callback per gestirli
        $this->updateCallback($id,$extraUpdate);
        
        // Callback post-modifica
        $this->postUpdateCallback($id, $data, $extraUpdate);

        $this->db->transComplete();

        // Se la transazione è fallita sollevo un eccezione
        if ( $this->db->transStatus() === FALSE ) {
            throw new UpdateException();
        }

        return $isUpdate;
    }

    //--------------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     * 
     */
    public function delete(IncomingRequest $request, int $id) : bool {
        
        // Controllo se la risorsa esiste
        if  ( is_null($oldData = $this->model->find($id)) ) {
            throw new ResourceNotFoundException();
        }

        // Inizializzo la transazione
        $this->db->transStart();

        // Funzione pre-cancellazione
        $this->preDeleteCallback($id, $oldData);

        // Cancello la riga e controllo se la funzione restituisce false
        $isDelete = $this->model->delete($id) != false;

        // Check per il logger
        $this->logger->create('delete', $id, $oldData);
        
        // Callback per ulteriori azioni di cancellazione
        $this->deleteCallback($id, $oldData);

        // Callback per ulteriori azioni post cancellazione
        $this->postDeleteCallback($id, $oldData);

        $this->db->transComplete();

        // Se la transazione è fallita sollevo un eccezione
        if ( $this->db->transStatus() === FALSE ) {
            throw new DeleteException();
        }

        return $isDelete;
    }

    //------------------------------------------------------------------------------------------------------

    /**
     * Funzione per il recupero di dati extra in caso di create/update
     * 
     * @param array $data   Dati delle richiesta
     * 
     * @return array 
     */
    public function getExtraData(array &$data ) {

        $extra = [];

        // Ciclo l'array data per trovare sottoarray,
        // se ci sono li estraggo
        foreach ( $data as $key => $value ) {

            if ( is_array($value) ) {
                $extra[$key] = $value;
                unset($data[$key]);
            }
        }

        return $extra;
    }

    //------------------------------------------------------------------------------------------------------

    /**
     * Funzione che gestisce la validazione sia in insert che in update.
     * 
     * @param string $action    Azione che si sta compiendo Default ('insert')
     * 
     * @return void
     */
    protected function checkValidation(IncomingRequest $request, string $action = 'insert') {

        // Recupero i dati 
        $data = $request->getJSON(TRUE);

        // Recupero le regole di validazioni generiche
        $generics = isset($this->validationsRules['generic']) ? $this->validationsRules['generic'] : []; 

        // Eseguo il merge delle regole di validazioni
        $rules = isset($this->validationsRules[$action]) 
            ? $generics + $this->validationsRules[$action]
            : $generics;

        // Se non esistono regole di validazione allora la salto
        if ( ! empty($rules)) {

            $validation = \Config\Services::validation();
            
            // Vengono settate le regole di validazione, con i messaggi di default
            $validation->setRules($rules,$this->validationsCustomMessage);
            
            // Lancio la validazione, se fallisce lancio un eccezione
            if ( ! $validation->run($data)) {
                throw new ValidationException($validation->getErrors());
            }
        }
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * Callback per la gestione dell'inserimento dei dati extra
     * 
     * @param array $data   Array con i dati extra da inserire
     * 
     * @return void
     * 
     * @deprecated  utilizzare postInsertCallback
     */
    protected function insertCallback( array $data ) : void  {} 

    //----------------------------------------------------------------------------------------------------


    /**
     * Callback eseguita pre inserimento dei dati
     * 
     * @param array $data   Array con i dati da inserire
     * lla riga inserita
     * @param array $data       Array con i dati 
     * @return array
     */
    protected function preInsertCallback( array $data ) : array  {
        return $data;
    } 

    //----------------------------------------------------------------------------------------------------


    /**
     * Callback eseguita post inserimento dei dati
     * 
     * @param int   $id         Identificativo della riga inserita
     * @param array $data       Array con i dati da inserire
     * @param array $extraData  Array che contiene i dati extra se ci sono( Default = [])
     * 
     * @return void
     */
    protected function postInsertCallback( int $id, array $data, array $extraData = []) : void  {} 

    //----------------------------------------------------------------------------------------------------


    /**
     * Callback eseguita pre modifica dei dati
     * 
     * @param int   $id     Identificativo della riga da modificare
     * @param array $data   Array con i dati per la modifica
     * 
     * @return array
     */
    protected function preUpdateCallback( int $id, array $data ) : array  {
        return $data;
    } 

    //----------------------------------------------------------------------------------------------------

    /**
     * Callback eseguita post modifica dei dati
     * 
     * @param int   $id         Identificativo della riga inserita
     * @param array $data       Array con i dati extra da inserire
     * @param array $extraData  Array che contiene i dati extra se ci sono( Default = [])
     * 
     * @return void
     */
    protected function postUpdateCallback( int $id, array $data, array $extraData = []) : void  {} 

    //----------------------------------------------------------------------------------------------------

    /**
     * Callback per la gestione delle modifiche dei dati extra
     * 
     * @param array $id     Identificativo della risorsa modificata
     * @param array $data   Array con i dati extra da inserire
     * 
     * @return void
     * 
     * @deprecated  utilizzare postUpdateCallback
     */
    protected function updateCallback( int $id, array $data ) : void  {} 

    //---------------------------------------------------------------------------------------------------
    
    /**
     * Callback eseguita pre cancellazione dei dati
     * 
     * @param int   $id     Identificativo della riga da cancellare
     * @param array $data   Array con i dati per della riga da cancellare
     * 
     * @return void
     */
    protected function preDeleteCallback( int $id, array $data ) : void  {} 

    //----------------------------------------------------------------------------------------------------

    /**
     * Callback per la gestione delle modifiche dei dati extra
     * 
     * @param int   $id     Identificativo risorsa cancellata
     * @param array $data   Dati della risorsa cancellata
     * 
     * @return void
     * 
     * @deprecated  utilizzare postDeleteCallback
     */
    protected function deleteCallback( int $id, array $data ) : void  {}
    
    //----------------------------------------------------------------------------------------------------

    /**
     * Callback eseguita post cancellazione dei dati
     * 
     * @param int   $id     Identificativo risorsa cancellata
     * @param array $data   Dati della risorsa cancellata
     * 
     * @return void
     */
    protected function postDeleteCallback( int $id, array $data ) : void  {}

    //---------------------------------------------------------------------------------------------------
    
    /**
     * Callback per effettuare aggiunte o modifiche alle query
     * 
     * @param array $options    Array con le opzioni per le query
     * @param array &$params    Array contentente i parametri per la lista
     * 
     * @return array    Opzioni modificate
     */
    protected function preRetrieveCallback(array $options, array &$params ) : array {
        return $options;
    } 

    //---------------------------------------------------------------------------------------------------

    /**
     * Callback per la gestione della lista post-query
     * 
     * @param array $data               Lista dei dati estratti
     * @param bool  $isSingleResource   True se sto recuperando un singolo elemento, False altrimenti (Default False)
     * 
     * @return array
     * 
     * @deprecated  utilizzare postRetrieveCallback
     */
    protected function retrieveCallback( array $data, bool $isSingleResource = false ) : array  {
        return $data;
    } 

    //---------------------------------------------------------------------------------------------------

    /**
     * Callback per la gestione della lista post-query
     * 
     * @param array $data               Lista dei dati estratti
     * @param bool  $isSingleResource   True se sto recuperando un singolo elemento, False altrimenti (Default False)
     * 
     * @return array
     */
    protected function postRetrieveCallback( array $data, bool $isSingleResource = false ) : array  {
        return $data;
    } 

    //---------------------------------------------------------------------------------------------------

    /**
     * Restituisce TRUE se il logger è attiva, FALSE altrimenti
     * 
     * @return bool
     */
    public function isActiveLogger() : bool {
        return $this->activeCrudLogger;
    }

    //---------------------------------------------------------------------------------------------------
}
