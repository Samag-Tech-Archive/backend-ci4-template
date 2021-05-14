<?php namespace SamagTech\Crud\Exceptions;

use Exception;

/**
 * Eccezione per validazione in fase di creazione e modifica dei dati.
 * 
 * @author Alessandro Marotta
 */
class DeleteException extends Exception {

    /**
     * Messaggio di default se non è settato nel costruttore
     * 
     * @var string
     */
    private string $customMessage = 'Errore di cancellazione della risorsa';
    
    /**
     * Codice di errore di default per un eccezione
     * in fase di validazione
     * 
     * @var int
     * 
     * Default 1
     */
    private int $customCode = 4;

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