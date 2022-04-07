<?php namespace SamagTech\Crud\Libraries\RestfulCaller;

use Exception;

/**
 * Classe per le eccezione della libreria RestfulCaller
 *
 * @method getStatusCode()
 *
 */
class RestfulCallerException extends Exception {

    /**
     * Codice di stato della richiesta
     *
     * @var int
     *
     * @access private
     */
    private int $statusCode;

    //---------------------------------------------------------------------------------------------------

    /**
     * Costruttore.
     *
     */
    public function __construct(string $message, int $statusCode = 400) {

        $this->statusCode = $statusCode;
        parent::__construct($message);

    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Restituisce il codice di stato della richiesta
     *
     * @return int
     */
    public function getStatusCode () : int {
        return $this->statusCode;
    }

    //---------------------------------------------------------------------------------------------------
}