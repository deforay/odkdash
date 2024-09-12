<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Application\Model;

use Laminas\Config\Factory;
use Laminas\Permissions\Acl\Acl as LaminasAcl;
use Laminas\Permissions\Acl\Resource\GenericResource;
use Laminas\Permissions\Acl\Role\GenericRole;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;

/**
 * Description of Acl
 *
 * @author amit
 */
class Acl extends LaminasAcl
{

    // public function __construct($resourceList, $rolesList)
    // {
    //     foreach ($resourceList as $res) {
    //         if (!$this->hasResource($res['resource_id'])) {
    //             $this->addResource(new GenericResource($res['resource_id']));
    //         }
    //     }

    //     foreach ($rolesList as $rol) {
    //         if (!$this->hasRole($rol['role_code'])) {
    //             $this->addRole(new GenericRole($rol['role_code']));
    //         }
    //     }

    //     $config = Factory::fromFile(CONFIG_PATH . DIRECTORY_SEPARATOR . "acl.config.php");

    //     foreach ($config as $role => $resources) {
    //         if (!$this->hasRole($role)) {
    //             $this->addRole(new GenericRole($role));
    //         }
    //         foreach ($resources as $resource => $permissions) {
    //             // $resource = stripcslashes($resource);
    //             foreach ($permissions as $privilege => $permission) {
    //                 $this->$permission($role, $resource, $privilege);
    //             }
    //         }
    //     }

    //     if (!$this->hasRole('daemon')) {
    //         $this->addRole('daemon');
    //     }

    //     $this->allow('daemon');
    // }
   
    public function __construct($resourceList, $rolesList, $rolePrivileges, $privileges)
    {
        foreach ($resourceList as $res) {
            if (!$this->hasResource($res['resource_id'])) {
                $this->addResource(new GenericResource($res['resource_id']));
            }
        }

        foreach ($rolesList as $rol) {
            if (!$this->hasRole($rol['role_code'])) {
                $this->addRole(new GenericRole($rol['role_code']));
            }
        }
     
        // Map privileges to resource and privilege names
        $privilegeMap = [];
        foreach ($privileges as $priv) {
            $privilegeMap[$priv['privilege_id']] = [
                'resource_id' => $priv['resource_id'],
                'privilege_name' => $priv['privilege_name']
            ];
        }

        $result = [];

        // Initialize all roles and resources
        foreach ($rolesList as $role) {
            $roleCode = $role['role_code'];
            $result[$roleCode] = [];

            foreach ($resourceList as $res) {
                $resourceId = $res['resource_id'];
                $result[$roleCode][$resourceId] = [];

                // Initialize only with privileges that exist for this resource
                // foreach ($privilegeMap as $privId => $privDetails) {
                //     if ($privDetails['resource_id'] === $resourceId) {
                //         $result[$roleCode][$resourceId][$privDetails['privilege_name']] = 'deny';
                //     }
                // }
            }
        }

        // Update privileges to 'allow' based on role-privilege mappings
        foreach ($rolePrivileges as $rp) {
            $roleId = $rp['role_id'];
            $privilegeId = $rp['privilege_id'];

            // Find the role code by role_id
            $roleCode = null;
            foreach ($rolesList as $role) {
                if ($role['role_id'] == $roleId) {
                    $roleCode = $role['role_code'];
                    break;
                }
            }

            // If role code and privilege mapping is found, update the result
            if ($roleCode && isset($privilegeMap[$privilegeId])) {
                $resourceId = $privilegeMap[$privilegeId]['resource_id'];
                $privilegeName = $privilegeMap[$privilegeId]['privilege_name'];

                // Ensure the resource is in the result array
                if (!isset($result[$roleCode][$resourceId])) {
                    $result[$roleCode][$resourceId] = [];
                }

                // Set the privilege to 'allow'
                $result[$roleCode][$resourceId][$privilegeName] = 'allow';
            }
        }
        $config = $result;
        // $config = Factory::fromFile(CONFIG_PATH . DIRECTORY_SEPARATOR . "acl.config.php");
        // echo "<pre>"; print_r($config); die;
        foreach ($config as $role => $resources) {
            if (!$this->hasRole($role)) {
                $this->addRole(new GenericRole($role));
            }
            foreach ($resources as $resource => $permissions) {
                // $resource = stripcslashes($resource);
                foreach ($permissions as $privilege => $permission) {
                    $this->$permission($role, $resource, $privilege);
                }
            }
        }
      
        if (!$this->hasRole('daemon')) {
            $this->addRole('daemon');
        }

        $this->allow('daemon');
    }
}
