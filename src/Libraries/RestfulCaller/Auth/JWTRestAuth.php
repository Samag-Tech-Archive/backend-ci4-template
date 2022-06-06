<?php namespace SamagTech\Crud\Libraries\RestfulCaller\Auth;

/**
 * Autenticazione tramite JWT
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
class JWTRestAuth extends BaseRestAuth {

    //---------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     * Con il JWT viene definito il Bearer Token
     */
    public function getHeader(?string $token = null): array {
        return ['headers' => ['Authorization' => 'bearer '. ($token ?? $this->token)]];
    }
}