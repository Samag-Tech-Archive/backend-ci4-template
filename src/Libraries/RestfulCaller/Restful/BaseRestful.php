<?php namespace SamagTech\Crud\Libraries\RestfulCaller\Restful;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use SamagTech\Crud\Libraries\RestfulCaller\Auth\BaseRestAuth;

/**
 * Definizione della gestione della chiamante Restful
 *
 * Vengono definite le esecuzioni di base delle funzioni.
 *
 * @abstract
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
abstract class BaseRestful implements RestfulInterface {

    /**
     * Client che esegue le richieste
     *
     * @access protected
     *
     * @var GuzzleHttp\Client
     */
    protected Client $client;

    /**
     * Oggetto per la gestione dell'autenticazione
     *
     * @access protected
     *
     * @var BaseRestAuth|null
     */
    protected ?BaseRestAuth $restAuth = null;

    //---------------------------------------------------------------------------------------------------

    /**
     * Costruttore.
     *
     * @param GuzzleHttp\Client $client     Istanza del client
     */
    public function __construct(Client $client, ?BaseRestAuth $restAuth = null) {
        $this->client = $client;
        $this->restAuth = $restAuth;
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function get(?array $query = null) : ResponseInterface| PromiseInterface {
        return $this->request('GET', data:$query);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function getById(int|string $id) : ResponseInterface | PromiseInterface {
        return $this->request('GET', $this->buildUrl($id));
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function post(?array $body = null) : ResponseInterface | PromiseInterface {
        return $this->request('POST', data: $body);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function put(int|string $id, array $body) : ResponseInterface | PromiseInterface {
        return $this->request('PUT', $this->buildUrl($id), $body);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function delete(int|string $id) : ResponseInterface | PromiseInterface {
        return $this->request('DELETE', $this->buildUrl($id));
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Esegue la richiesta REST
     *
     * @access protected
     *
     * @abstract
     *
     * @param string $method    Metodo della richiesta
     * @param string $path      Path della richiesta
     * @param array|null $data  Dati da inviare alla richiesta
     *
     * @return ResponseInterface|PromiseInterface di Guzzle;
     */
    abstract protected function request (string $method, string $path = '', ?array $data = null) : ResponseInterface | PromiseInterface;

    //---------------------------------------------------------------------------------------------------

    /**
     * Costruisce l'URL da chiamare in base al path per la richiesta
     *
     * @param string $path Path della richiesta
     *
     * @return string
     */
    protected function buildUrl(string $path) : string {
        return str_ends_with($this->client->getBaseUrl(), '/') ? $this->client->getBaseUrl().$path : $this->client->getBaseUrl().'/'.$path;
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Costruisci la richiesta in base al metodo.
     *
     * @access protected
     *
     * @param string $method    Metodo della richiesta
     * @param array<string,mixed>|null $request     Richiesta
     *
     * @return array<string,mixed>
     */
    protected function buildRequest(string $method, ?array $request = null) : array {

        $format = [];

        if ( ! empty($request)) {

            $format = match(strtoupper($method)) {
                'GET' => ['query' => $request],
                'POST', 'PUT' => ['json' => $request]
            };
        }

        if ( ! is_null($this->restAuth) ) {
            $format = array_merge($format, $this->restAuth->getHeader());
        }

        return $format;
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Controlla se il metodo della richiesta è valido
     *
     * @access protected
     *
     * @param string $method    Metodo della richiesta
     *
     * @return bool TRUE se è valido, FALSE altrimenti
     */
    protected function isValidMethod ( string $method) : bool {
        return in_array($method, self::METHOD_ALLOWED);
    }

    //---------------------------------------------------------------------------------------------------
}