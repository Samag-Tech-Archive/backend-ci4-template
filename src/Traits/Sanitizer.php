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
    protected array $sanitizeConfig = [];

    /**
     * Chiavi per l'array di configurazione
     */
    private array $sanitizeConfigKeys = [
        "required",
        "null_field",
        "optional",
        "messages"
    ];

    //----------------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function create(IncomingRequest $request): array
    {
        $data = $request->getJSON(TRUE);

        $this->checkValidation($data, 'insert');

        $data = $this->preInsertCallback($data);

        // eseguo il sanitize dei dati pre inserimento nel database
        $data = $this->check($data);

        $extraInsert = $this->getExtraData($data);

        $this->db->transStart();

        if ($this->model->useCreatedBy) {
            $data = array_merge($data, [
                'created_by' => $this->currentUser->id,
            ]);
        }

        $id = $this->model->insert($data);

        $this->logger->create('create', $id, $data);

        $this->insertCallback($extraInsert);

        $this->postInsertCallback($id, $data, $extraInsert);

        $this->db->transComplete();

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
        $data = $this->check($data);

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

    //----------------------------------------------------------------------------------------------------

    /**
     * Modifica della configurazione,
     *
     * È possibile passare più elementi come fields passando un'array
     *
     * E.g:
     *  [
     *      "required" => [
     *          "campo"
     *      ],
     *      "optional" => [
     *          "campo"
     *      ]
     * ];
     *
     * @return void
     */
    protected function changeSetup(string|array $field, ?array $params = null): void
    {
        if (is_string($field)) {
            $field = [$field => $params];
        }
        foreach ($field as $configKey => $configParams) {
            $this->isValidSetup($configKey);
            $this->sanitizeConfig[$configKey] = $configParams;
        }
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * Verifica validità del campo su cui eseguire il sanitize
     *
     * @return bool
     * @throws GenericException
     */
    protected function isValidSetup(string $field): bool
    {
        if (!in_array($field, $this->sanitizeConfigKeys)) {
            throw new GenericException("Il campo $field non è supportato", 500);
        }
        return true;
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * Funzione che effettua i vari check sui dati
     * prima dell'inserimento nel database
     *
     * @param  array $data
     * @return array
     */
    protected function check(array $data): array
    {
        $this->initSetup();
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
     * Check array di configurazione
     *
     * @return void
     */
    private function initSetup(): void
    {
        foreach ($this->sanitizeConfigKeys as $key) {
            isset($this->sanitizeConfig[$key]) ?: $this->sanitizeConfig[$key] = [];
        }
    }

    //----------------------------------------------------------------------------------------------------

    /**
     * Funzione che elimina tutti gli elementi non utili alla registrazione del prodotto
     *
     * @param  array $data
     * @return void
     */
    private function removeNotRequired(array &$data): void
    {
        foreach ($data as $key => $value) {
            if ($this->checkOptional($key)) {
                if (!in_array($key, $this->sanitizeConfig['required'])) {
                    unset($data[$key]);
                }
            }
        }
    }

    //----------------------------------------------------------------------------------------------------

    /**
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
     * Check sui campi required
     *
     * @param  array $data
     * @return void
     * @throws GenericException
     */
    private function checkRequired(array &$data): void
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
     * Funzione che setta a null i dati nell'array null_field
     *
     * @param  array $data
     * @return void
     */
    private function setToNull(&$data): void
    {
        if (empty($this->sanitizeConfig['null_field'])) {
            return;
        }
        foreach ($this->sanitizeConfig['null_field'] as $key => $field) {
            $data[$field] = null;
        }
    }
}
