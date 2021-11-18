<?php namespace SamagTech\Crud\Core;

use CodeIgniter\Controller as Controller;
use \CodeIgniter\HTTP\Response as Response;
use CodeIgniter\API\ResponseTrait;
use SamagTech\Crud\Exceptions\CreateException;
use SamagTech\Crud\Exceptions\DeleteException;
use SamagTech\Crud\Exceptions\GenericException;
use SamagTech\Crud\Exceptions\ResourceNotFoundException;
use SamagTech\Crud\Exceptions\UpdateException;
use SamagTech\Crud\Exceptions\ValidationException;
use SamagTech\Crud\Singleton\CurrentUser;
use SamagTech\Crud\Traits\CrudTrait;

/**
 * Classe astratta per la definizione di un nuovo CRUD.
 *
 * @implements CrudInterface
 * @extends Controller
 * @abstract
 */
abstract class BulkServiceController extends ServiceController implements BulkServiceControllerInterface {

    /**
     * Array contenente i messaggi di default
     * per le risposte delle API
     *
     * @var array
     * @access public
     */
    public array $messages = [
        'create'                =>  'La risorsa è stata creata',
        'retrieve'              =>  'Lista risorse',
        'update'                =>  'La risorsa è stata modificata',
        'delete'                =>  'La risorsa è stata cancellata',
        'bulk_create'           =>  'Tutte le risorse sono state caricate',
        'bulk_update'           =>  'Tutte le risorse sono state modificate',
        'bulk_delete'           =>  'Tutte le risorse sono state cancellate',
        'bulk_create_error'     =>  'Tutte le risorse sono state caricate',
        'bulk_update_error'     =>  'Tutte le risorse sono state modificate',
        'bulk_delete_error'     =>  'Tutte le risorse sono state cancellate',
    ];

    //--------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function bulkCreate() : Response {

        try {
            $created = $this->service->bulkCreate($this->request);
        }
        catch(ValidationException $e) {
            return $this->failValidationErrors($e->getValidationErrors(), $e->getHttpCode());
        }
        catch(CreateException | GenericException $e) {
            return $this->fail($e->getMessage(), $e->getHttpCode());
        }

        $message = $created ? $this->messages['bulk_create'] : $this->messages['bulk_create_error'];

        return $this->respondCreated(['message' => $message], $message);

    }

    //--------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function bulkUpdate() : Response {

        try {

            $updated = $this->service->bulkUpdate($this->request);
        }
        catch(ValidationException $e) {
            return $this->failValidationErrors($e->getValidationErrors(), $e->getHttpCode());
        }
        catch ( ResourceNotFoundException $e ) {
            return $this->failNotFound($e->getMessage(), $e->getHttpCode());
        }
        catch(UpdateException | GenericException $e) {
            return $this->fail($e->getMessage(), $e->getHttpCode());
        }

        $message = $updated ? $this->messages['bulk_update'] : $this->messages['bulk_update_error'];

        return $this->respondUpdated(['message' => $message], $message);
    }

    //--------------------------------------------------------------------------------------------

    /**
     * {@inheritDoc}
     *
     */
    public function bulkDelete() : Response {

        try {
            $deleted = $this->service->bulkDelete($this->request);
        }
        catch ( ResourceNotFoundException $e ) {
            return $this->failNotFound($e->getMessage(), $e->getHttpCode());
        }
        catch(DeleteException | GenericException $e) {
            return $this->fail($e->getMessage(), $e->getHttpCode());
        }

        $message = $deleted ? $this->messages['bulk_delete'] : $this->messages['bulk_delete_error'];

        return $this->respondDeleted(['message' => $message], $message);
    }

}