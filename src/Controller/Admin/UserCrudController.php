<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PasswordField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;
    private Security $security;

    public function __construct(UserPasswordHasherInterface $passwordHasher, Security $security)
    {
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            TextField::new('username'),
            TextField::new('email'),
            ArrayField::new('roles'),
        ];

        if (in_array($pageName, ['new', 'edit'])) {
            $fields[] = TextField::new('plainPassword', 'Password')
                ->setFormType(PasswordType::class)
                ->setHelp('Leave empty to keep the current password')
                ->onlyOnForms()
                ->setRequired($pageName === 'new');
        }

        return $fields;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handlePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handlePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handlePassword(User $user): void
    {
        if ($user->getPlainPassword()) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashedPassword);
            $user->eraseCredentials(); // Clear the plain password to avoid storing it
        }
    }

}
