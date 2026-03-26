<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\DTO\Auth\CreateUserDTO;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserService implements UserServiceInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ){}

    public function createUser(array $userData): void
    {
        $userDto = CreateUserDTO::process($userData);
        
        $user = $this->userRepository->findUserByEmail($userDto->email);
        
        if(!empty($user)) {
            throw new BadRequestHttpException('User already registered.');
        }

        $this->userRepository->create($userDto);
    }

    public function findUser(int $userId): User
    {
        $user = $this->userRepository->findUser($userId);
        
        if($user === null) {
            throw new BadRequestHttpException('Invalid Data.');
        }

        return $user;
    }
}
