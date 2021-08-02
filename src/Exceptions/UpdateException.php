<?php namespace SamagTech\Crud\Exceptions;

/**
 * Eccezione per validazione in fase di creazione e modifica dei dati.
 * 
 * @author Alessandro Marotta
 */
class UpdateException extends AbstractCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     * 
     * @var string
     */
    private string $customMessage = 'Errore di modifica della risorsa';
    
}