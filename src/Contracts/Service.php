<?php namespace SamagTech\Contracts;

use CodeIgniter\Entity\Entity;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Definisce l'istanza di un servizio.
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
interface Service {

    //-------------------------------------------------------------------------------------------------------

    /**
     * Crea una nuova risorsa.
     *
     * @param  IncomingRequest $request      Dati della richiesta
     *
     * @throws ValidationException   Solleva questa eccezione se è fallita la validazione
     * @throws CreateException       Solleva quest'eccezione se c'è stato un errore durante la creazione
     * @throws GenericException      Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return Entity|array<string,mixed> Ritorna i dati della risorsa appaena Creata
     */
    public function create(IncomingRequest $request) : Entity|array;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Restituisce una lista di risorse.
     *
     * @param  IncomingRequest $request    Dati di parametri e filtri
     *
     * @throws GenericException          Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return array<string,mixed>|Entity[]
     */
    public function retrieve(IncomingRequest $request) : array;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Restituisce i dati di una singola risorsa.
     *
     * @param int|string   $id     Identificativo della risorsa.
     *
     * @throws ResourceNotFountException Solleva questa eccezione se la risorsa non esiste.
     * @throws GenericException          Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return Entity|array<string,mixed>
     */
    public function retrieveById(int|string $id) : Entity|array;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Modifica una risorsa esistente in base all'identificativo
     *
     * @param  int|string       $id           Identificativo della risorsa
     * @param  IncomingRequest  $request      Dati della richiesta
     *
     * @throws UpdateException               Solleva quest'eccezione se c'è stato un errore durante la modifica
     * @throws ValidationException           Solleva questa eccezione se è fallita la validazione
     * @throws ResourceNotFoundException     Solleva questa eccezione se la risorsa non esiste
     * @throws GenericException              Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return bool  Ritorna TRUE se la risorsa è stata modifica, FALSE altrimenti
     */
    public function update(IncomingRequest $request, int|string $id) : bool;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Cancella la risorsa in base all'identificativo
     *
     * @param  int|string      $id           Identificativo della risorsa
     * @param  IncomingRequest $request      Dati della richiesta
     *
     * @throws DeleteException               Solleva quest'eccezione se c'è stato un errore durante la cancellazione
     * @throws ResourceNotFoundException     Solleva questa eccezione se la risorsa non esiste
     * @throws GenericException              Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return bool  Ritorna TRUE se la risorsa è stata cancellata, FALSE altrimenti
     */
    public function delete(IncomingRequest $request, int $id) : bool;

    //---------------------------------------------------------------------------------------------------

    /**
     * Esportazione della lista in formato Excel
     *
     * @param IncomingRequest $request   Dati della richiesta
     *
     * @throws ExcelException solleva quest'eccezione in caso di fallimento della creazione dell'excel
     *
     * @param string    Path per scaricare l'excel
     */
    public function export(IncomingRequest $request) : string;

    //---------------------------------------------------------------------------------------------------
}