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
     * @param  CodeIgniter\HTTP\IncomingRequest $request      Dati della richiesta
     *
     * @throws \SamagTech\Exceptions\ValidationException   Solleva questa eccezione se è fallita la validazione
     * @throws \SamagTech\Exceptions\CreateException       Solleva quest'eccezione se c'è stato un errore durante la creazione
     * @throws \SamagTech\Exceptions\GenericException      Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return CodeIgniter\Entity\Entity|array<string,mixed> Ritorna i dati della risorsa appaena Creata
     */
    public function create(IncomingRequest $request) : Entity|array;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Restituisce una lista di risorse.
     *
     * @param  CodeIgniter\HTTP\IncomingRequest $request    Dati di parametri e filtri
     *
     * @throws \SamagTech\Exceptions\GenericException          Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return array<string,mixed>|CodeIgniter\Entity\Entity[]
     */
    public function retrieve(IncomingRequest $request) : array;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Restituisce i dati di una singola risorsa.
     *
     * @param int|string   $id     Identificativo della risorsa.
     *
     * @throws \SamagTech\Exceptions\ResourceNotFountException Solleva questa eccezione se la risorsa non esiste.
     * @throws \SamagTech\Exceptions\GenericException          Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return CodeIgniter\Entity\Entity|array<string,mixed>
     */
    public function retrieveById(int|string $id) : Entity|array;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Modifica una risorsa esistente in base all'identificativo
     *
     * @param  int|string       $id           Identificativo della risorsa
     * @param  CodeIgniter\HTTP\IncomingRequest  $request      Dati della richiesta
     *
     * @throws \SamagTech\Exceptions\UpdateException               Solleva quest'eccezione se c'è stato un errore durante la modifica
     * @throws \SamagTech\Exceptions\ValidationException           Solleva questa eccezione se è fallita la validazione
     * @throws \SamagTech\Exceptions\ResourceNotFoundException     Solleva questa eccezione se la risorsa non esiste
     * @throws \SamagTech\Exceptions\GenericException              Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return bool  Ritorna TRUE se la risorsa è stata modifica, FALSE altrimenti
     */
    public function update(IncomingRequest $request, int|string $id) : bool;

    //--------------------------------------------------------------------------------------------------------

    /**
     * Cancella la risorsa in base all'identificativo
     *
     * @param  int|string      $id           Identificativo della risorsa
     * @param  CodeIgniter\HTTP\IncomingRequest $request      Dati della richiesta
     *
     * @throws \SamagTech\Exceptions\DeleteException               Solleva quest'eccezione se c'è stato un errore durante la cancellazione
     * @throws \SamagTech\Exceptions\ResourceNotFoundException     Solleva questa eccezione se la risorsa non esiste
     * @throws \SamagTech\Exceptions\GenericException              Solleva quest'eccezione se c'è stato un errore generico
     *
     * @return bool  Ritorna TRUE se la risorsa è stata cancellata, FALSE altrimenti
     */
    public function delete(IncomingRequest $request, int|string $id) : bool;

    //---------------------------------------------------------------------------------------------------

    /**
     * Esportazione della lista in formato Excel
     *
     * @param CodeIgniter\HTTP\IncomingRequest $request   Dati della richiesta
     *
     * @throws SamagTech\ExcelLib\ExcelException solleva quest'eccezione in caso di fallimento della creazione dell'excel
     *
     * @param string    Path per scaricare l'excel
     */
    public function export(IncomingRequest $request) : string;

    //---------------------------------------------------------------------------------------------------
}