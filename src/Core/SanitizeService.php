<?php

namespace SamagTech\Crud\Core;

use SamagTech\Crud\Traits\CleanRequest;

class SanitizeService extends CRUDService
{
    use CleanRequest;

    protected array $config;

    public function __construct(){
        parent::__construct();
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    protected function preUpdateCallback(int $id, array $data): array
    {
        return $this->setup($this->config)->check($data);
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    protected function preInsertCallback(array $data): array
    {
        return $this->setup($this->config)->check($data);
    }
}
