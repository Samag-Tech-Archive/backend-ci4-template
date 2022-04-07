<?php namespace SamagTech\Crud\Libraries\RestfulCaller\Executor;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use SamagTech\Crud\Exceptions\GenericException;
use SamagTech\Crud\Libraries\RestfulCaller\Restful\BaseRestful;

/**
 * Classe per l'esecuzione di richieste sincrone
 *
 * @extend BaseRestfulCaller
 *
 * @property Client $client
 *
 * @method ResponseInterface|PromiseInterface get($query = null)               Lista di risorse
 * @method ResponseInterface|PromiseInterface getById($id)                     Dati singola risorsa
 * @method ResponseInterface|PromiseInterface post($body)                      Creazione di una risorsa
 * @method ResponseInterface|PromiseInterface put($id, $body)                  Modifica di una risorsa
 * @method ResponseInterface|PromiseInterface delete($id)                      Cancellazione di una risorsa
 * @method string                             buildUrl($path)                  Costruisce l'URL per la richiesta
 * @method array<string,mixed>                buildRequest($method, $request)  Costruisce la richiesta
 * @method bool                               isValidMethod($method)           Controlla se un metodo è valido
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
class SyncRestful extends BaseRestful {

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    protected function request (string $method, string $path = '', ?array $data = null) : ResponseInterface | PromiseInterface {

        $method = strtoupper($method);

        if ( ! $this->isValidMethod($method) ) {
            throw new GenericException('Il metodo della richiesta non è valido');
        }

        $req = $this->buildRequest($method, $data);

        return $this->client->request($method,$path, $req);
    }

    //---------------------------------------------------------------------------------------------------

}