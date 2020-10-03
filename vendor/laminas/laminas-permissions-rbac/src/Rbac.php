<?php

/**
 * @see       https://github.com/laminas/laminas-permissions-rbac for the canonical source repository
 * @copyright https://github.com/laminas/laminas-permissions-rbac/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-permissions-rbac/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Permissions\Rbac;

use RecursiveIteratorIterator;

class Rbac extends AbstractIterator
{
    /**
     * flag: whether or not to create roles automatically if
     * they do not exist.
     *
     * @var bool
     */
    protected $createMissingRoles = false;

    /**
     * @param  bool                     $createMissingRoles
     * @return \Laminas\Permissions\Rbac\Rbac
     */
    public function setCreateMissingRoles($createMissingRoles)
    {
        $this->createMissingRoles = $createMissingRoles;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCreateMissingRoles()
    {
        return $this->createMissingRoles;
    }

    /**
     * Add a child.
     *
     * @param  string|RoleInterface               $child
     * @param  array|RoleInterface|null           $parents
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function addRole($child, $parents = null)
    {
        if (is_string($child)) {
            $child = new Role($child);
        }
        if (! $child instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Child must be a string or implement Laminas\Permissions\Rbac\RoleInterface'
            );
        }

        if ($parents) {
            if (! is_array($parents)) {
                $parents = [$parents];
            }
            foreach ($parents as $parent) {
                if ($this->createMissingRoles && ! $this->hasRole($parent)) {
                    $this->addRole($parent);
                }
                $this->getRole($parent)->addChild($child);
            }
        }

        $this->children[] = $child;

        return $this;
    }

    /**
     * Is a child with $name registered?
     *
     * @param  \Laminas\Permissions\Rbac\RoleInterface|string $objectOrName
     * @return bool
     */
    public function hasRole($objectOrName)
    {
        try {
            $this->getRole($objectOrName);

            return true;
        } catch (Exception\InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Get a child.
     *
     * @param  \Laminas\Permissions\Rbac\RoleInterface|string $objectOrName
     * @return RoleInterface
     * @throws Exception\InvalidArgumentException
     */
    public function getRole($objectOrName)
    {
        if (! is_string($objectOrName) && ! $objectOrName instanceof RoleInterface) {
            throw new Exception\InvalidArgumentException(
                'Expected string or implement \Laminas\Permissions\Rbac\RoleInterface'
            );
        }

        if (is_object($objectOrName)) {
            $requiredRole = $objectOrName->getName();
        } else {
            $requiredRole = $objectOrName;
        }

        $it = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $leaf) {
            /** @var RoleInterface $leaf */
            if ($leaf->getName() == $requiredRole) {
                return $leaf;
            }
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'No role with name "%s" could be found',
            is_object($objectOrName) ? $objectOrName->getName() : $objectOrName
        ));
    }

    /**
     * Determines if access is granted by checking the role and child roles for permission.
     *
     * @param  RoleInterface|string             $role
     * @param  string                           $permission
     * @param  AssertionInterface|Callable|null $assert
     * @throws Exception\InvalidArgumentException
     * @return bool
     */
    public function isGranted($role, $permission, $assert = null)
    {
        $result = $this->getRole($role)->hasPermission($permission);

        if ($assert) {
            if ($assert instanceof AssertionInterface) {
                return $result && $assert->assert($this);
            }

            if (is_callable($assert)) {
                return $result && $assert($this);
            }

            throw new Exception\InvalidArgumentException(
                'Assertions must be a Callable or an instance of Laminas\Permissions\Rbac\AssertionInterface'
            );
        }

        return $result;
    }
}
