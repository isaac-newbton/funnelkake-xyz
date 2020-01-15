<?php

namespace App\Service\UserRole;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class UserRoleHandler {

	public function __construct(RoleHierarchyInterface $roleHierarchy)
	{
		$this->roleHierarchy = $roleHierarchy;
	}

	public function getInheritedRoles(string $role){
		// fetch roles for specified $role
		return $this->roleHierarchy->getReachableRoleNames([$role]);
	}
}