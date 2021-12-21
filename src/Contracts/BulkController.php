<?php namespace SamagTech\Contracts;

use \CodeIgniter\HTTP\Response;

/**
 * Definizione di un controller con le funzionalità bulk.
 *
 * @interface
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 */
interface BulkServiceController {

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la creazione bulk di più risorse
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function bulkCreate() : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la modifica bulk di più risorse
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function bulkUpdate() : Response;

    //--------------------------------------------------------------------------------------------

    /**
     * Route per la cancellazione bulk di più risorse
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function bulkDelete() : Response;

    //---------------------------------------------------------------------------------------------------
}