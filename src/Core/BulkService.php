<?php namespace SamagTech\Crud\Core;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Interfaccia per la definizione di metodi per funzionalità
 * bulk
 *
 * @interface
 *
 * @author Alessandro Marotta
 */
interface BulkService {

    //-------------------------------------------------------------------------------------------------------

    /**
     * Funzione che dovrà creare un nuova risorsa.
     *
     * @param  IncomingRequest $request      Dati della richiesta
     *
     * @throws ValidationException   Solleva questa eccezione se è fallita la validazione
     * @throws CreateException       Solleva quest'eccezione se c'è stato un errore durante la creazione
     * @throws GenericException      Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return array  Ritorna l'array contente tutti i dati inseriti
     */
    public function bulkCreate(IncomingRequest $request) : bool;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Funzione che dovrà modifica una risorsa esistente
     *
     * @param  int              $id           Identificativo della risorsa
     * @param  IncomingRequest $request      Dati della richiesta
     *
     * @throws UpdateException               Solleva quest'eccezione se c'è stato un errore durante la modifica
     * @throws ValidationException           Solleva questa eccezione se è fallita la validazione
     * @throws ResourceNotFoundException     Solleva questa eccezione se la risorsa non esiste
     * @throws GenericException              Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return bool  Ritorna TRUE se la risorsa è stata modifica, FALSE altrimenti
     */
    public function bulkUpdate(IncomingRequest $request) : bool;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Funzione che dovrà cancellare una risorsa esistente
     *
     * @param  int              $id           Identificativo della risorsa
     * @param  IncomingRequest $request      Dati della richiesta
     *
     * @throws DeleteException               Solleva quest'eccezione se c'è stato un errore durante la cancellazione
     * @throws ResourceNotFoundException     Solleva questa eccezione se la risorsa non esiste
     * @throws GenericException              Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return bool  Ritorna TRUE se la risorsa è stata cancellata, FALSE altrimenti
     */
    public function bulkDelete(IncomingRequest $request) : bool;
}