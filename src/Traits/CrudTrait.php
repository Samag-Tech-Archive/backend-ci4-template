<?php namespace SamagTech\Crud\Traits;

trait CrudTrait {

    //---------------------------------------------------------------------------------------------------

    /**
     * Restituisce il nome della classe
     * 
     * @param bool  withNamespace   Flag che indica se restituire il namespace Default False
     * 
     * @return string
     */
    public function getClassName(bool $withNamespace = false ) : string {

        if ( $withNamespace ) {
            return __CLASS__; 
        }
        else {
            $path = explode('\\', __CLASS__);
            return array_pop($path);
        }
    }

    //---------------------------------------------------------------------------------------------------


}