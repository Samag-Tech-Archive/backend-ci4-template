<?php namespace SamagTech\Crud\Config;

use CodeIgniter\Config\BaseConfig;

class Application extends BaseConfig {

    /**
     * Flag per attivare e disabilitare il logging
     * 
     * @var bool 
     */
    public bool $activeLogger = true;

    /**
     * Path per gli upload
     * 
     * @var string
     */
    public string $uploadsPath = WRITEPATH.'uploads/';

    /**
     * Path per la cartella temporanea
     * 
     */
    public string $tmpPath = WRITEPATH.'tmp/';

    /**
     * Flag che indica se le api sono
     * in manutenzione.
     * 
     * @var bool
     */
    public bool $maintenance = false;

}