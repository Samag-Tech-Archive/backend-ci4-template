<?php namespace SamagTech\Crud\Singleton;

/**
 * Singleton per l'utente autentificato.
 * Contiene i dati decodificati del JWT.
 *
 * @author Alessandro Marotta
 */
class CurrentUser {

    /**
     * Istanza del singleton
     *
     * @var CurrentUser
     * @access private
     */
    private static ?self $istance = null;

    /**
     * Oggetto contentente il decode del JWT
     *
     * @var object
     * @access private
     */
    private static ?object $property = null;

    //-------------------------------------------------------------------------

    /**
     * Costruttore
     *
     */
    private function __construct() {}

    //-------------------------------------------------------------------------

    /**
     * Funzione che restituisce l'istanza della classe
     *
     * @access public
     * @return CurrentUser
     */
    public static function getIstance() : CurrentUser {

        // Controllo se l'istanza è già impostata,
        // nel in caso contrario la istanzio
        if ( self::$istance == null ) {
            $class = __CLASS__;
            self::$istance = new $class;
        }

        return self::$istance;
    }

    //------------------------------------------------------------------------

    /**
     * Funzione per settare le proprietà dell'utente autentifcato
     *
     * @params object $property     Oggetto generato dal decode del JWT
     * @return void
     */
    public function setProperty(object $property) : void {
        self::$property = $property;
    }

    //------------------------------------------------------------------------

    /**
     * Restituisce le proprietà dell'utente autenticato
     *
     * @return object
     */
    public function getProperty() {
        return self::$property;
    }

    //------------------------------------------------------------------------

    /**
     * Restituisce le proprietà in formato json
     *
     * @return string
     */
    public function toJson() : string {
        return json_encode(self::$property);
    }

    //------------------------------------------------------------------------
}