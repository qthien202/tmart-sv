<?php
namespace App\Http\Controllers\V1\Auth\Models;

use App\UserLog;

/**
 * Class UserLogModel
 *
 * @package App\V1\CMS\Models
 */
class UserLogModel extends AbstractModel {
    public function __construct(UserLog $model = null) {
        parent::__construct($model);
    }
}