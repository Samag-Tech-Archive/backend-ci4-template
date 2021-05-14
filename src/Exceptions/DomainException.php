<?php namespace SamagTech\Crud\Exceptions;

use Exception;

/**
 * Eccezione per validazione dei domini
 * 
 * @author Alessandro Marotta
 */
class DomainException extends Exception {

    /**
     * Messaggio di default se non è settato nel costruttore
     * 
     * @var string
     */
    private string $customMessage = 'L\'accesso a questo dominio è vietato';
    
    /**
     * Codice di errore di default per un eccezione
     * in fase di validazione
     * 
     * @var int
     * 
     * Default 1
     */
    private int $customCode = 6;

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
        $code   ??= $this->customCode;

        parent::__construct($message, $code, $previous);
    }
}