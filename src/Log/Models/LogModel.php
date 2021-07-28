<?php namespace SamagTech\Log\Models;

use SamagTech\Log\Config\Log;

/**
 * CodeIgniter Logger
 *
 * @package Log
 * @author  Alessandro Marotta <alessandro.marotta@samag.tech>
 * 
 */
class LogModel extends \CodeIgniter\Model {

    //------------------------------------------------------------------

    /**
     * Costruttore.
     * 
     */
    public function __construct() {
        parent::__construct();

        // Imposto la tabella su cui agire
        $config = new Log();
        $this->table = $config->table;

        // Disattivo la protezione
        $this->protect(false);
    }

    //------------------------------------------------------------------

}