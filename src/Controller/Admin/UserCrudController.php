<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $email = TextField::new('email');
        $roles = ArrayField::new('roles');
        $passwordOnUpdate = TextField::new('password')
            ->setFormType(PasswordType::class)
            ->setRequired(true);
        $passwordOnCreate = TextField::new('password')
            ->setFormType(PasswordType::class)
            ->setRequired(true);
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            return [
                $email,
//                $roles,
                (Crud::PAGE_EDIT === $pageName) ? $passwordOnUpdate : $passwordOnCreate,
            ];
        }

        return [
            $email,
            $roles,
        ];
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param                        $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePlainPassword($entityInstance);

        if ($entityInstance instanceof User) {
            $entityInstance->setRoles(["role" => "ROLE_ADMIN"]);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param $entityInstance
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePlainPassword($entityInstance);

        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * @param $entityInstance
     */
    private function encodePlainPassword($entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPassword()) {
            $entityInstance->setPassword(
                $this->passwordEncoder->encodePassword($entityInstance, $entityInstance->getPassword())
            );
        }
    }

}
