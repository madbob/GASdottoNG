<?php

namespace app\Services;

use App\Exceptions\AuthException;
use App\Exceptions\PermissionException;
use App\Permission;

class PermissionsService
{

    public function showForSubject($subject_id, $rule_id)
    {
        $class = Permission::classByRule($rule_id);
        if ($class == null) {
            throw new PermissionException('Rule not found');
        }

        $subject = $class::findOrFail($subject_id);
        if ($subject->permissionsCanBeModified() == false) {
            throw new AuthException(401);
        }

        return $subject->whoCanComplex($rule_id);
    }

}
