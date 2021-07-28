<?php namespace SamagTech\Crud\Core;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Definizione di un interfaccia di un servizio per
 * la gestione di risorse.
 * 
 * @interface 
 *
 * @author Alessandro Marotta
 */
interface Service {

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
     * @return int  Identificativo della risorsa appena creata
     */
    public function create(IncomingRequest $request) : int; 

    //--------------------------------------------------------------------------------------------------------

    /**
     * Funzione che dovrà recuperare una o più risorse in base 
     * a se l'identificativo di una risorsa è impostato o meno.
     * 
     * @param  int              $id          Identificativo risorsa (Default 'null')
     * @param  IncomingRequest $request     Dati della richiesta  
     * 
     * @throws ResourceNotFountException Solleva questa eccezione se la risorsa non esiste.
     * @throws GenericException          Solleva quest'eccezione se c'è stato un errore generico
     * 
     * @return array Lista delle risorse cercate
     */
    public function retrieve(IncomingRequest $request, int $id = null) : array; 

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
    public function update(IncomingRequest $request, int $id) : bool; 

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
    public function delete(IncomingRequest $request, int $id) : bool; 
}