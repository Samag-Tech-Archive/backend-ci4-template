<?php namespace SamagTech\Crud\Libraries\RestfulCaller\Restful;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Interfaccia per la definizione di un oggetto
 * che gestisce le richieste.
 *
 * Utilizza i paramentri di Guzzle
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
interface RestfulInterface {

    /**
     * Lista dei metodi gestiti
     *
     * @const
     *
     * @var array<string>
     */
    const METHOD_ALLOWED = [
        'GET',
        'POST',
        'PUT',
        'DELETE'
    ];

    //---------------------------------------------------------------------------------------------------

    /**
     * Esegue la richieste di una lista di risorse in base alla query da eseguire
     *
     * @param array<string,string>|null $query  Lista dei parametri da inviare alla richiesta
     *
     * @throws GenericException solleva questa eccezione in caso di errori
     *
     * @return ResponseInterface|PromiseInterface classi di Guzzle
     */
    public function get(?array $query = null) : ResponseInterface | PromiseInterface;

    //---------------------------------------------------------------------------------------------------

    /**
     * Esegue la richiesta di una risorsa in base al suo identificativo
     *
     * @param int|string $id  ID della risorsa
     *
     * @throws GenericException solleva questa eccezione in caso di errori
     *
     * @return ResponseInterface|PromiseInterface classi di Guzzle
     */
    public function getById(int|string $id) : ResponseInterface | PromiseInterface;

    //---------------------------------------------------------------------------------------------------

    /**
     * Esegue la richiesta di creazione di una risorsa
     *
     * @param array|null $body  Dati per la creazione della risorsa
     *
     * @throws GenericException solleva questa eccezione in caso di errori
     *
     * @return ResponseInterface|PromiseInterface classi di Guzzle
     */
    public function post(?array $body = null) : ResponseInterface | PromiseInterface;

    //---------------------------------------------------------------------------------------------------

    /**
     * Esegue la richiesta di modifica di una risorsa
     *
     * @param int|string $id    ID della risorsa
     * @param array|null $body  Dati per la creazione della risorsa
     *
     * @throws GenericException solleva questa eccezione in caso di errori
     *
     * @return ResponseInterface|PromiseInterface classi di Guzzle
     */
    public function put(int|string $id, array $body) : ResponseInterface | PromiseInterface;

    //---------------------------------------------------------------------------------------------------

    /**
     * Esegue la richiesta di cancellazione una risorsa
     *
     * @param int|string $id    ID della risorsa
     *
     * @throws GenericException solleva questa eccezione in caso di errori
     *
     * @return ResponseInterface|PromiseInterface classi di Guzzle
     */
    public function delete(int|string $id) : ResponseInterface | PromiseInterface;

}