<?php namespace SamagTech\Crud\Libraries\RestfulCaller\Caller;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use InvalidArgumentException;
use SamagTech\Crud\Config\RestfulCaller as ConfigRestfulCaller;
use SamagTech\Crud\Libraries\RestfulCaller\Auth\BaseRestAuth;
use SamagTech\Crud\Libraries\RestfulCaller\Auth\RestAuthInterface;
use SamagTech\Crud\Libraries\RestfulCaller\RestfulCallerException;
use SamagTech\Crud\Libraries\RestfulCaller\Restful\RestfulInterface;

/**
 * Definizione di un caller di base
 *
 * @method array<string,mixed> get($query = null)  Lista di risorse
 * @method array<string,mixed> getById($id)        Dati singola risorsa
 * @method array<string,mixed> post($body)         Creazione di una risorsa
 * @method bool                put($id, $body)     Modifica di una risorsa
 * @method bool                delete($id)         Cancellazione di una risorsa
 *
 * @abstract
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
abstract class BaseCaller implements CallerInterface {

    /**
     * Client che esegue le richieste
     *
     * @var GuzzleHttp\Client;
     * @access protected
     */
    protected Client $client;

    /**
     * Url di base da chiamare
     *
     * Default ''
     *
     * @var string
     * @access protected
     */
    protected string $baseUrl = '';

    /**
     * Tipologia di autenticazione da utilizzare
     *
     * Le opzioni sono:
     *   - 'api_key'    per utilizzare X-API-KEY
     *   - 'jwt'        per utilizzare Authorization
     *
     * @param string
     *
     * @access protected
     */
    protected string $authType = 'api_key';

    /**
     * Tipologia di caller da utilizzare.
     *
     *
     * Le opzioni di default sono:
     *      - 'sync' per utilizzare il caller sincrono
     *      - 'async' per utilizzare il caller asincrono
     *
     * Default 'sync'
     *
     * @var string
     *
     * @access protected
     */
    protected string $callerType = 'sync';

    /**
     * Token per l'auteticazione
     *
     * @param string|null
     *
     * @access private
     */
    private ?string $authToken = null;

    /**
     * Istanza che esegue le richieste
     *
     * @var RestfulInterface
     *
     * @access private
     */
    private RestfulInterface $executorRequest;

    //---------------------------------------------------------------------------------------------------

    /**
     * Costruttore
     *
     */
    public function __construct() {

        // Viene creato il client
        $this->client = new Client(['base_uri' => $this->baseUrl]);

        // Viene creato l'esecutore delle richieste
        $this->executorRequest = $this->createExecutorRequest();
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Imposta il tipo di caller da utilizzare
     *
     * @param string $callerType
     *
     * @return self
     */
    public function setCallerType(string $callerType) : self {
        $this->callerType = $callerType;
        $this->executorRequest = $this->createExecutorRequest();
        return $this;
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Imposta il tipo di autenticazione da utilizzare
     *
     * @param string $authType
     *
     * @return self
     */
    public function setAuthType(string $authType) : self {

        if ( ! in_array($authType, ['api_key', 'jwt']) ) {
            throw new InvalidArgumentException('Il tipo di autenticazione non esiste');
        }

        $this->authType = $authType;

        return $this;
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Imposta il token di autenticazione
     *
     * @param string $authToken
     *
     * @return self
     */
    public function setAuthToken(string $authToken) : self {

        $this->authToken = $authToken;
        $this->executorRequest = $this->createExecutorRequest();

        return $this;
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     *
     */
    public function get(?array $query = null) : array|PromiseInterface {

        $response = $this->executorRequest->get($query);

        if ($response instanceof PromiseInterface) {
            return $response;
        }

        [$statusCode, $body] = $this->getResponseData($response);

        if ( $statusCode == 200 ) {
            return $body;
        }

        throw new RestfulCallerException($body->messages->error, $statusCode);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     *
     */
    public function getById(int|string $id) : array|PromiseInterface {

        $response = $this->executorRequest->getById($id);

        if ($response instanceof PromiseInterface) {
            return $response;
        }

        [$statusCode, $body] = $this->getResponseData($response);

        if ( $statusCode == 200 ) {
            return $body;
        }

        throw new RestfulCallerException($body->messages->error, $statusCode);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     *
     */
    public function post(?array $body = null) : array|PromiseInterface {


        $response = $this->executorRequest->post($body);

        if ($response instanceof PromiseInterface) {
            return $response;
        }

        [$statusCode, $body] = $this->getResponseData($response);

        if ( $statusCode == 201 ) {
            return $body;
        }

        throw new RestfulCallerException($body->messages->error, $statusCode);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     *
     */
    public function put(int|string $id, array $body) : bool|PromiseInterface {

        $response = $this->executorRequest->put($id, $body);

        if ($response instanceof PromiseInterface) {
            return $response;
        }

        [$statusCode, $body] = $this->getResponseData($response);

        if ( $statusCode == 200 ) {
            return true;
        }

        throw new RestfulCallerException($body->messages->error, $statusCode);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     *
     */
    public function delete(int|string $id) : bool|PromiseInterface {

        $response = $this->executorRequest->delete($id);

        if ($response instanceof PromiseInterface) {
            return $response;
        }

        [$statusCode, $body] = $this->getResponseData($response);

        if ( $statusCode == 200 ) {
            return true;
        }

        throw new RestfulCallerException($body->messages->error, $statusCode);
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Restituisce il parsing della risposta
     *
     * @access private
     *
     * @param ResponseInterface|PromiseInterface $response     Risposta della chiamata
     *
     * @return array<int,int|object>
     */
    private function getResponseData(ResponseInterface|PromiseInterface $response) : array {
        return [
            $response->getStatusCode(),
            json_decode($response->getBody())
        ];
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Crea l'istanza dell'esecutore di richieste
     *
     * @access private
     *
     * @return RestfulInterface
     */
    private function createExecutorRequest() : RestfulInterface {

        // Recupera la configurazione
        $config = new ConfigRestfulCaller;

        // Se il tipo non è presente nella configurazione allora sollevo un eccezione
        if ( ! isset($config->defaults[$this->callerType]) || ! isset($config->anotherCaller[$this->callerType]) ) {
            throw new RestfulCallerException('Il tipo di caller non esiste');
        }

        // Se il tipo è presente nei caller custom lo recupero da lì altrimenti recupero dai default
        if ( isset($config->customCallers[$this->callerType]) ) {
            return new ${$config->customCallers[$this->callerType]}($this->client, new BaseRestAuth($this->authToken));
        }

        return new ${$config->defaults[$this->callerType]}($this->client, new BaseRestAuth($this->authToken));
    }
}