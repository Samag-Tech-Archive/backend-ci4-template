<?php namespace SamagTech\Crud\Exceptions;

use Exception;

/**
 * Eccezione per validazione in fase di creazione e modifica dei dati.
 * 
 * @author Alessandro Marotta
 */
class ValidationException extends Exception {

    /**
     * Messaggio di default se non è settato nel costruttore
     * 
     * @var string
     * @access private
     */
    private string $customMessage = 'Errore di validazione';
    
    /**
     * Codice di errore di default per un eccezione
     * in fase di validazione
     * 
     * @var int
     * @access private
     * Default 1
     */
    private int $customCode = 1;

    /**
     * Array contentente gli errori di validazione
     * 
     * @var array 
     * @access private
     */
    private array $errors = [];

    /**
     * Costruttore.
     * 
     * @param array     $errors    Array con gli errori
     * @param string    $message   Messaggio dell'eccezione (Default 'null') 
     * @param int       $code      Codice di errore dell'eccezione ( Default 'null') 
     * @param Exception $previous  Eccezione precedente (Default 'null')
     */
    public function __construct( array $errors = [], $message = null, $code = null, Exception $previous = null ) {

        // Controllo se è settato il messaggio
        $message ??= $this->customMessage;

        // Controllo se è settato il codice
        $code   ??= $this->customCode;

        // Setto l'array degli errori
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    //---------------------------------------------------------------------------
    
    /**
     * Funzione che restituisce il primo errore 
     * di validazione.
     * 
     * @return string
     */
    public function getValidationError() : string {
        return current($this->errors);
    }
}