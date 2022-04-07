<?php namespace SamagTech\Crud\Libraries\RestfulCaller\Auth;

/**
 * Autenticazione tramite X-API-KEY
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
class APIKeyRestAuth implements RestAuthInterface {

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     * Con il JWT viene definito il Bearer Token
     */
    public function getHeader(?string $token = null): array {
        return ['headers' => ['X-API-KEY' => $token ?? $this->token]];
    }
}