<?php namespace SamagTech\Crud\Core;

use SamagTech\Crud\Contracts\Service;

/**
 * Definizione di un interfaccia per l'applicazione
 * del pattern Factory per la creazione dinamica
 * del servizio
 *
 * @interface
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
interface Factory {

    /**
     * Restituisce l'istanza del Service in base al token.
     *
     * @param string|null $token    Token che identifica quale servizio istanziare
     *
     * @throws BadFactoryException  Solleva quest'eccezione se non esiste un servizio di default se il token è null
     *
     * @return \SamagTech\Crud\Core\Service Restituisce una classe CRUDService
     */
    public function makeService( ?string $token = null) : Service;

}