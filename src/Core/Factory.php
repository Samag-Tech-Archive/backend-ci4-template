<?php namespace SamagTech\Crud\Core;

/**
 * Definizione di un interfaccia per l'applicazione
 * del pattern Factory per la creazione dinamica 
 * del servizio giusto
 * 
 * @interface 
 *
 * @author Alessandro Marotta
 */
interface Factory {

    /**
     * Funzione che restituisce il servizio da chiamare
     * in base ad un token.
     * 
     * @param string $token Token che identifica quale servizio istanziare
     * 
     * @return CRUDService Restituisce una classe CRUDService
     */
    public function getFactory( string $token ) : CRUDService;
}