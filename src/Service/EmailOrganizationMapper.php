<?php
namespace App\Service;

use App\Repository\UserRepository;

class EmailOrganizationMapper{
	public function findOrganization(string $email, UserRepository $user_repository){
		if($user = $user_repository->findOneByEmail($email)){
			return $user->getOrganization();
		}
		return false;
	}
}