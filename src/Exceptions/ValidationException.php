<?php namespace SamagTech\Exceptions;

/**
 * Eccezione utilizzata per la gestione della validazione durante
 * le fasi di creazioni e modifica di una risorsa
 *
 * @author Alessandro Marotta <alessandro.marotta@samag.tech>
 *
 * @extends \SamagTech\Exceptions\BaseCrudException
 */
class ValidationException extends BaseCrudException {

    /**
     * Messaggio di default se non è settato nel costruttore
     *
     * @var string
     * @access protected
     */
    protected string $customMessage = 'Errore di validazione';

    /**
     * Codice di errore da restituire come status http
     *
     * @var int
     *
     * @access protected
     *
     * Default 422-UNPROCESSABLE ENTITY
     */
    protected int $httpCode = 422;

    /**
     * Array contentente gli errori di validazione oppure il signolo
     * errore di validazione.
     *
     * @var array|string
     * @access private
     */
    private array|string $errors = [];

    /**
     * Costruttore.
     *
     * @param array<string,string>|string     $errors    Array con gli errori oppure singolo errore
     * @param string    $message   Messaggio dell'eccezione (Default 'null')
     * @param int       $code      Codice di errore dell'eccezione ( Default 'null')
     * @param Exception $previous  Eccezione precedente (Default 'null')
     */
    public function __construct( array|string $errors = [], $message = null, $code = null, \Exception $previous = null ) {

        // Controllo se è settato il messaggio
        $message ??= $this->customMessage;

        // Controllo se è settato il codice
        $code   ??= $this->httpCode;

        // Setto l'array degli errori
        $this->errors = $errors;

        parent::__construct($message, $code, $previous);
    }

    //---------------------------------------------------------------------------

    /**
     * Restituisce il singolo errore di validazione.
     *
     * Se gli errori sono contenuti in un array allora viene recuperato il primo
     *
     * @return string
     */
    public function getValidationError() : string {
        return is_string($this->errors) ? $this->errors : current($this->errors);
    }

    //---------------------------------------------------------------------------

    /**
     * Funzione che restituisce l'array degli errori di validazione
     *
     * @return array
     */
    public function getValidationErrors() : array {
        return $this->errors;
    }

    //---------------------------------------------------------------------------
}
