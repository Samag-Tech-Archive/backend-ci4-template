<?php namespace SamagTech\Crud\Exceptions;

/**
 * Eccezione per validazione in fase di creazione e modifica dei dati.
 * 
 * @author Alessandro Marotta
 */
class ResourceNotFoundException extends AbstractCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     * 
     * @var string
     */
    private string $customMessage = 'La risorsa non è stata trovata';
    
    /**
     * Codice di errore da restituire come status http
     * 
     * @var int
     * 
     * Default 400-Not Found
     */
    private int $httpCode = 404;

}