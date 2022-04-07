<?php namespace SamagTech\Crud\Libraries\RestfulCaller\Caller;

use GuzzleHttp\Promise\PromiseInterface;

/**
 * Interfaccia per la definizione dei metodi di default
 * di un caller.
 *
 * Utilizza i paramentri di Guzzle
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
interface CallerInterface {

    //---------------------------------------------------------------------------------------------------

    /**
     * Restituisce una lista di risorse in base alla query da eseguire
     *
     * @param array<string,string>|null $query  Lista dei parametri da inviare alla richiesta
     *
     * @throws SamagTech\Crud\Libraries\RestfulCaller\RestfulCallerException    Solleva questa eccezione se si ha un errore
     *
     * @return array<string,mixed>|PromiseInterface
     */
    public function get(?array $query = null) : array|PromiseInterface;

    //---------------------------------------------------------------------------------------------------

    /**
     * Restituisce una risorsa in base al suo identificativo
     *
     * @param int|string $id  ID della risorsa
     *
     * @throws SamagTech\Crud\Libraries\RestfulCaller\RestfulCallerException    Solleva questa eccezione se si ha un errore
     *
     * @return array<string,mixed>|PromiseInterface
     */
    public function getById(int|string $id) : array|PromiseInterface;

    //---------------------------------------------------------------------------------------------------

    /**
     * Creo una risorsa
     *
     * @param array|null $body  Dati per la creazione della risorsa
     *
     * @throws SamagTech\Crud\Libraries\RestfulCaller\RestfulCallerException    Solleva questa eccezione se si ha un errore
     *
     * @return array<string,mixed>|PromiseInterface
     */
    public function post(?array $body = null) : array|PromiseInterface;

    //---------------------------------------------------------------------------------------------------

    /**
     * Modifico una risorsa
     *
     * @param int|string $id    ID della risorsa
     * @param array|null $body  Dati per la creazione della risorsa
     *
     * @throws SamagTech\Crud\Libraries\RestfulCaller\RestfulCallerException    Solleva questa eccezione se si ha un errore
     *
     * @return bool|PromiseInterface
     */
    public function put(int|string $id, array $body) : bool|PromiseInterface;

    //---------------------------------------------------------------------------------------------------

    /**
     * Cancella una risorsa
     *
     * @param int|string $id    ID della risorsa
     *
     * @throws SamagTech\Crud\Libraries\RestfulCaller\RestfulCallerException    Solleva questa eccezione se si ha un errore
     *
     * @return bool|PromiseInterface
     */
    public function delete(int|string $id) : bool|PromiseInterface;

}