<?php

declare(strict_types=1);

namespace App\Http;

use Application\Model\Acl;

/**
 * Which pages may call each helper action.
 *
 * A helper is an action that a page's own JavaScript calls, such as a table
 * feed or a dropdown fill, and that has no privilege in the privileges table.
 * The role edit screen cannot grant it, so it inherits from its callers: a
 * role that may open any page calling the helper may call the helper.
 *
 * The map is generated from the templates by bin/dev/helper-access into
 * config/helper-access.php. `composer qc` fails when it is out of date.
 */
final class HelperAccess
{
    /** @var array<string, array<string, list<array{0: string, 1: string}>>> */
    private array $parents;

    public function __construct(string $file)
    {
        $this->parents = is_file($file) ? require $file : [];
    }

    /** True when the role may open one of the pages that call this helper. */
    public function allows(Acl $acl, string $role, string $resource, string $action): bool
    {
        foreach ($this->parents[$resource][Acl::normalise($action)] ?? [] as [$parentResource, $parentPrivilege]) {
            if ($acl->isAllowed($role, $parentResource, $parentPrivilege)) {
                return true;
            }
        }

        return false;
    }
}
