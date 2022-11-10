<?php

namespace SamagTech\Crud\Traits;

use SamagTech\Crud\Exceptions\GenericException;

/**
 * Ripulisce i dati della richiesta
 */
trait CleanRequest
{
    public function setup(array $setup): self
    {
        $this->config['required']   = $setup['required'] ?? [];
        $this->config['null_field'] = $setup['null_field'] ?? [];
        $this->config['optional']   = $setup['optional'] ?? [];
        $this->config['messages']   = $setup['messages'] ?? [];

        return $this;
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * check
     *
     * Funzione che effettua i vari check sui dati
     * prima dell'inserimento nel database
     *
     * @param  array $data
     * @return array
     */
    public function check(array $data): array
    {
        if (isset($this->config['required'])) {
            $this->removeNotRequired($data);
            $this->checkRequired($data);
        }

        if (isset($this->config['null_field'])) {
            $this->setToNull($data);
        }

        return $data;
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * removeNotRequired
     *
     * Funzione che elimina tutti gli elementi non utili alla registrazione del prodotto
     *
     * @param  array $data
     * @return array
     */
    private function removeNotRequired(array &$data)
    {
        foreach ($data as $key => $field) {
            if ($this->checkOptional($key)) {
                if (!in_array($key, $this->config['required'])) {
                    unset($data[$key]);
                }
            }
        }
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * checkOptional
     *
     * Valida che il campo passato non si trovi nell'array dei campi opzionali
     *
     * @param  mixed $field
     * @return bool
     */
    private function checkOptional($field)
    {
        if (isset($this->config['optional'])) {
            return !in_array($field, $this->config['optional']);
        }
        return true;
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * checkRequired
     *
     * Check sui campi richiesti per la registrazione del prodotto
     *
     * @param  array $data
     * @return array
     */
    private function checkRequired(array &$data)
    {
        foreach ($this->config['required'] as $key => $field) {
            if (!in_array($field, array_keys($data))) {
                if (in_array($field, $this->config['optional'])) {
                    return;
                }
                throw new GenericException($this->config['messages'][$field] ?? "Sembra che il campo $field non sia presente, controlla i dati inseriti e riprova", 400);
            }

            if (is_null($data[$field]) || empty($data[$field])) {
                throw new GenericException($this->config['messages'][$field] ?? "Sembra che il campo $field sia vuoto, controlla i dati inseriti e riprova", 400);
            }
        }
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * setToNull
     *
     * Funzione che setta a null i dati nell'array setToNull
     *
     * @param  array $data
     * @return array
     */
    private function setToNull(&$data)
    {
        if (empty($this->config['null_field'])) {
            return;
        }
        foreach ($this->config['null_field'] as $key => $field) {
            $data[$field] = null;
        }
    }
}
