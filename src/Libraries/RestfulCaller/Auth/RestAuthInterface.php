<?php namespace SamagTech\Crud\Libraries\RestfulCaller\Auth;

/**
 * Interfaccia per la definizione del tipo di autenticazione da utilizzare
 * nelle chiamate REST
 * *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
interface RestAuthInterface {

    /**
     * Restituisce la configurazione dell'header
     *
     * @param string|null $token     Valore del token
     *
     * @return array<string,string>
     */
    public function getHeader(?string $token = null) : array;

}