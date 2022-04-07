<?php
namespace SamagTech\Crud\Casts;

use CodeIgniter\Entity\Cast\BaseCast;

class EntityCast extends BaseCast {

    public static function get($value, array $params = []) {

        $model = new $params[0];

        return $model->find($value);
    }
}