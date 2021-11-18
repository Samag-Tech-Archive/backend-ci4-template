<?php namespace SamagTech\Crud\Core;

use \CodeIgniter\HTTP\Response;

/**
 * Interfaccia per la definizione delle route principali
 * di un controller CRUD per la generazione di dati bulk
 *
 * @author Alessandro Marotta
 *
 */
interface BulkServiceControllerInterface {

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
}