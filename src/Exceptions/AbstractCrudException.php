<?php namespace SamagTech\Crud\Exceptions;

use Exception;

/**
 * Classe per la gestione delle eccezioni del CRUD
 *
 * @author Alessandro Marotta
 * @abstract
 */
abstract class AbstractCrudException extends Exception {

    /**
     * Messaggio di default se non è settato nel costruttore
     *
     * @var string
     */
    protected string $customMessage = 'C\'è stato un errore';

    /**
     * Codice di errore da restituire come status http
     *
     * @var int
     *
     * Default 400-Bad Request
     */
    protected int $httpCode = 400;

    //-------------------------------------------------------------------------------------------------------

    /**
     * Costruttore.
     *
     * @param string    $message   Messaggio dell'eccezione (Default 'null')
     * @param int       $code      Codice di errore dell'eccezione ( Default 'null')
     * @param Exception $previous  Eccezione precedente (Default 'null')
     */
    public function __construct($message = null, $code = null, Exception $previous = null ) {

        // Controllo se è settato il messaggio
        $message ??= $this->customMessage;

        // Controllo se è settato il codice
        $this->httpCode = ! is_null($code) ? $code : $this->httpCode;

        parent::__construct($message, $this->httpCode, $previous);
    }

    //-------------------------------------------------------------------------------------------------------

    /**
     * Funzione che restituisce il codice di errore http
     *
     * @return int
     */
    public function getHttpCode() : int {
        return $this->httpCode;
    }

    //-------------------------------------------------------------------------------------------------------
}