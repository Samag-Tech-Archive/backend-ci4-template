<?php

namespace SamagTech\Crud\Traits;

use CodeIgniter\I18n\Time;
use CodeIgniter\HTTP\IncomingRequest;
use SamagTech\Crud\Exceptions\CreateException;
use SamagTech\Crud\Exceptions\UpdateException;
use SamagTech\Crud\Exceptions\GenericException;
use SamagTech\Crud\Exceptions\ResourceNotFoundException;

/**
 * Ripulisce i dati della richiesta prima dell'inserimento nel database
 */
trait Sanitizer
{

    /**
     * Array contenente la configurazione da utilizzare,
     *
     * E.g:
     *  "required" => [     // per rendere i campi obbligatori all'inserimento
     *      "campo1",
     *      "campo2"
     *  ],
     *  "null_field" => [     // per settare i campi a NULL all'inserimento
     *      "campo1",
     *      "campo2"
     *  ],
     *  "optional" => [     // per un campo non obbligatorio all'inserimento
     *      "campo1",
     *      "campo2"
     *  ],
     *  "messages" => [     // per settare i messaggi di errore
     *      "campo1" => "Errore campo1",
     *      "campo2" => "Errore campo2"
     *  ]
     */
    public array $sanitizeConfig = [
        "required" => [],
        "null_field"=>[],
        "optional" => [],
        "messages" => [],
    ];

    //----------------------------------------------------------------------------------------------------

    /**
     * Setto i valori dell'array di configurazione
     */
    private function setup(array $setup): self
    {
        $this->sanitizeConfig['required']   = $setup['required'] ?? [];
        $this->sanitizeConfig['null_field'] = $setup['null_field'] ?? [];
        $this->sanitizeConfig['optional']   = $setup['optional'] ?? [];
        $this->sanitizeConfig['messages']   = $setup['messages'] ?? [];

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
    private function check(array $data): array
    {
        if (isset($this->sanitizeConfig['required'])) {
            $this->removeNotRequired($data);
            $this->checkRequired($data);
        }

        if (isset($this->sanitizeConfig['null_field'])) {
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
                if (!in_array($key, $this->sanitizeConfig['required'])) {
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
        if (isset($this->sanitizeConfig['optional'])) {
            return !in_array($field, $this->sanitizeConfig['optional']);
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
        foreach ($this->sanitizeConfig['required'] as $key => $field) {
            if (!in_array($field, array_keys($data))) {
                if (in_array($field, $this->sanitizeConfig['optional'])) {
                    return;
                }
                throw new GenericException($this->sanitizeConfig['messages'][$field] ?? "Sembra che il campo $field non sia presente, controlla i dati inseriti e riprova", 400);
            }

            if (is_null($data[$field]) || empty($data[$field])) {
                throw new GenericException($this->sanitizeConfig['messages'][$field] ?? "Sembra che il campo $field sia vuoto, controlla i dati inseriti e riprova", 400);
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
        if (empty($this->sanitizeConfig['null_field'])) {
            return;
        }
        foreach ($this->sanitizeConfig['null_field'] as $key => $field) {
            $data[$field] = null;
        }
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function create(IncomingRequest $request): array
    {

        // Recupero i dati dalla richiesta
        $data = $request->getJSON(TRUE);

        // Eseguo il check della validazione
        $this->checkValidation($data, 'insert');

        // Callback pre-inserimento
        $data = $this->preInsertCallback($data);

        // eseguo il sanitize dei dati pre inserimento nel database
        $data = $this->setup($this->sanitizeConfig)->check($data);

        // Callback per estrarre dati esterni alla riga
        $extraInsert = $this->getExtraData($data);

        // Inizializzo la transazione
        $this->db->transStart();

        // Se è impostato la colonna created_by aggiungo l'utente corrente
        if ($this->model->useCreatedBy) {
            $data = array_merge($data, [
                'created_by' => $this->currentUser->id,
            ]);
        }

        // Inserisco i dati
        $id = $this->model->insert($data);

        $this->logger->create('create', $id, $data);

        // Se esistono dati extra allora eseguo la callback per gestirli
        $this->insertCallback($extraInsert);

        // Callback post-inserimento
        $this->postInsertCallback($id, $data, $extraInsert);

        // Termino la transazione
        $this->db->transComplete();

        // Se la transazione è fallita sollevo un eccezione
        if ($this->db->transStatus() === FALSE) {
            throw new CreateException();
        }

        return $this->model->find($id);
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function update(IncomingRequest $request, int $id): bool
    {
        if (is_null($oldData = $this->model->find($id))) {
            throw new ResourceNotFoundException();
        }

        $data = $request->getJSON(TRUE);

        $this->checkValidation($data, 'update');

        $data = $this->preUpdateCallback($id, $data);

        // eseguo il sanitize dei dati pre inserimento nel database
        $data = $this->setup($this->sanitizeConfig)->check($data);

        $extraUpdate = $this->getExtraData($data);

        $this->db->transStart();

        if ($this->model->useUpdatedBy) {
            $data = array_merge($data, [
                'updated_by' => $this->currentUser->id,
            ]);
        }

        $data['updated_date'] = Time::now();

        $isUpdate = $this->model->update($id, $data);

        $this->logger->create('update', $id, $oldData, $data);

        $this->updateCallback($id, $extraUpdate);

        $this->postUpdateCallback($id, $data, $extraUpdate);

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            throw new UpdateException();
        }

        return $isUpdate;
    }
}
