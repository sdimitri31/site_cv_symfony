<?php
namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $userPasswordHasher;
    }

    public function createAdminUser(string $username, string $password, string $email): User
    {
        $user = $this->setUser($username, $password, $email);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function adminExists(): bool
    {
        $repository = $this->entityManager->getRepository(User::class);
        $admin = $repository->findOneBy(['roles' => 'ROLE_ADMIN']);
        return $admin !== null;
    }

    public function setUser(string $username, string $password, string $email):User
    {
        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['username' => $username]);

        if($user != null){
            $user->setRoles(['ROLE_ADMIN']);
        }
        else{
            $user = new User();
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setRoles(['ROLE_ADMIN']);
            $encodedPassword = $this->passwordHasher->hashPassword($user, $password);
            $user->setPassword($encodedPassword);
        }

        return $user;
    }
}
