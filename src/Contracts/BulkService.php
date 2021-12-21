<?php namespace SamagTech\Contracts;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Interfaccia per la definizione di metodi per funzionalità
 * bulk
 *
 * @interface
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
interface BulkService {

    //-------------------------------------------------------------------------------------------------------

    /**
     * Creazione bulk delle risorse
     *
     * @param  CodeIgniter\HTTP\IncomingRequest $request      Dati della richiesta
     *
     * @throws \SamagTech\Exceptions\ValidationException   Solleva questa eccezione se è fallita la validazione
     * @throws \SamagTech\Exceptions\CreateException       Solleva quest'eccezione se c'è stato un errore durante la creazione
     * @throws \SamagTech\Exceptions\GenericException      Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return array<int,array<string,mixed>>  Ritorna l'array contente tutti i dati inseriti
     */
    public function bulkCreate(IncomingRequest $request) : bool;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Modifica bulk di risorse
     *
     * @param  CodeIgniter\HTTP\IncomingRequest $request      Dati della richiesta
     *
     * @throws \SamagTech\Exceptions\UpdateException               Solleva quest'eccezione se c'è stato un errore durante la modifica
     * @throws \SamagTech\Exceptions\ValidationException           Solleva questa eccezione se è fallita la validazione
     * @throws \SamagTech\Exceptions\ResourceNotFoundException     Solleva questa eccezione se la risorsa non esiste
     * @throws \SamagTech\Exceptions\GenericException              Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return bool  Ritorna TRUE se la risorsa è stata modifica, FALSE altrimenti
     */
    public function bulkUpdate(IncomingRequest $request) : bool;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Cancellazione bulk di risorse.
     *
     * @param  CodeIgniter\HTTP\IncomingRequest $request      Dati della richiesta
     *
     * @throws \SamagTech\Exceptions\DeleteException               Solleva quest'eccezione se c'è stato un errore durante la cancellazione
     * @throws \SamagTech\Exceptions\ResourceNotFoundException     Solleva questa eccezione se la risorsa non esiste
     * @throws \SamagTech\Exceptions\GenericException              Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return bool  Ritorna TRUE se la risorsa è stata cancellata, FALSE altrimenti
     */
    public function bulkDelete(IncomingRequest $request) : bool;
}