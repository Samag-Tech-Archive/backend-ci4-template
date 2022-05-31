<?php namespace SamagTech\Crud\Libraries\RestfulCaller\Auth;

/**
 * Definizione della base per una classe RestAuth
 *
 * @abstract
 *
 * @method array<string,array<string,string> getHeader($token = null)
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 */
abstract class BaseRestAuth implements RestAuthInterface {

    /**
     * Token per l'autenticazione
     *
     * @var string
     *
     * @access protected
     */
    protected string $token;

    //---------------------------------------------------------------------------------------------------

    /**
     * Costuttore.
     *
     * @param string $token     Token per l'autenticazione
     */
    public function __construct(string $token) {
        $this->token = $token;
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Imposta il token
     *
     * @param string $token
     *
     * @return self
     */
    public function setToken (string $token) : self {
        $this->token = $token;
        return $this;
    }

    //---------------------------------------------------------------------------------------------------

    /**
     * Restituisce il token
     *
     * @return string|null
     */
    public function getToken() : ?string {
        return $this->token;
    }

    //---------------------------------------------------------------------------------------------------
}