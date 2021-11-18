<?php namespace SamagTech\Log\Libraries;

use SamagTech\Crud\Config\Application;
use SamagTech\Crud\Core\CRUDService;
use SamagTech\Crud\Singleton\CurrentUser;
use SamagTech\Log\Exceptions\LogException;
use SamagTech\Log\Config\Log as ConfigLog;
use SamagTech\Log\Models\LogModel;

/**
 * CodeIgniter Logger
 *
 * @package Log
 * @author  Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 */
class Log  {

    /**
     * Modello per il log
     *
     * @var LogModel
     * @access private
     */
    private LogModel $model;

    /**
     * Tabella da loggare
     *
     * @var string
     * @access private
     */
    private string $table;

    /**
     * Utente corrente
     *
     * @var CurrentUser
     * @access private
     */
    private CurrentUser $user;

    /**
     * Servizio che effettua genera il log
     *
     * @var CRUDService
     * @access private
     */
    private CRUDService $service;

    /**
     * Configurazione.
     *
     * @var ConfigLog
     * @access private
     */
    private ConfigLog $config;

    /**
     * Configurazione applicazione
     *
     * @var Application
     * @access private
     */
    private Application $appConfig;

    //---------------------------------------------------------------------------------------------

    /**
     * Costruttore.
     *
     * @param string $table     Tabella da loggare
     * @param int    $userID    Identificativo dell'utente che esegue l'azione
     *
     */
    public function __construct(string $table, CRUDService $service, CurrentUser $user = null) {

        // Istanzio il modello
        $this->model = new LogModel();

        // Setto le variabili iniziali
        $this->table        = $table;
        $this->user         = $user;
        $this->service      = $service;

        // Carico la configurazione
        $this->config = new ConfigLog();
        $this->appConfig = config('Application');
    }

    //---------------------------------------------------------------------------------------------

    /**
     * Imposta la tabella da loggare
     *
     * @param string $table
     * @return void
     */
    public function setTable(string $table) : void { $this->table = $table; }

    //---------------------------------------------------------------------------------------------

    /**
     * Restituisce la tabella di log
     *
     * @return string
     */
    public function getTable() : string { return $this->table; }

    //---------------------------------------------------------------------------------------------

    /**
     * Imposta l'identificativo utente
     *
     * @param CurrentUser $userID
     * @return void
     */
    public function setUser(CurrentUser $user) : void { $this->user = $user; }

    //---------------------------------------------------------------------------------------------

    /**
     * Restituisce l'identificativo utente.
     *
     * @return CurrentUser
     */
    public function getUser() : CurrentUser { return $this->user; }

    //---------------------------------------------------------------------------------------------


    /**
     * Imposta il servizio che genera il log
     *
     * @param CRUDService $service
     * @return void
     */
    public function setService(CRUDService $service) : void { $this->service = $service; }

    //---------------------------------------------------------------------------------------------

    /**
     * Restituisce l'identificativo utente.
     *
     * @return CRUDService
     */
    public function getService() : CRUDService { return $this->service; }

    //---------------------------------------------------------------------------------------------

    /**
     * Funzione per la creazione di un log
     *
     * @param string    $typeLog        Tipo di log
     * @param array     $old            Dati vecchi
     * @param array     $new            Dati nuovi (Default null in caso di creazione, cancellazione o eccezioni).
     *
     * @throws  LogException    Eccezione per i log
     *
     * @return void
     */
    public function create(string $typeLog, int $rowID, array $old, array $new = null) : void {

        // Controllo se il log deve essere generato
        if ( ! $this->appConfig->activeLogger && ! $this->service->isActiveLogger() ) {
            return;
        }

        // Controllo che la tabella e lo userID siano settati
        if ( is_null($this->table) ) {
            throw new LogException('La tabella da loggare non è impostata.');
        }

        // Controllo che la tipologia sia gestita
        if ( ! in_array($typeLog, $this->config->typeLogs, true) ) {
            throw new LogException('La tipologia di log non è gestita.');
        }

        /**
         * Se la tipologia è compresa da una di quelle considerate come modifica dei dati
         * allora i nuovi dati devono essere impostati
         *
         */
        if ( in_array($typeLog, $this->config->updatedType, true)  && ( is_null($new) || empty($new) ) ) {
            throw new LogException('In fase di update devono essere settati i nuovi dati');
        }

        // Dati da inserire nel log
        $toInsert = [
            'table'      => $this->table,
            'row_id'     => $rowID,
            'service'    => get_class($this->service),
            'old_data'   => json_encode($old),
            'new_data'   => is_null($new) ? $new : json_encode($new),
            'type'       => $typeLog,
            'user'       => $this->user?->toJson()
        ];

        // Inserisco i dati
        if ( ! $this->model->insert($toInsert) ) {
            throw new LogException('Errore durante l\'inserimento del log');
        }
    }

    //---------------------------------------------------------------------------------------------

}
