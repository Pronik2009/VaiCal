<?php

namespace App\Controller\Admin;

use App\Entity\City;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return City::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $name = TextField::new('name');
        $slug = SlugField::new('slug')
            ->setTargetFieldName('name')
        ;
        $years = AssociationField::new('years');

        if ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            return [
                $name, $slug,
            ];
        }

        return [
            $id, $name, $slug, $years,
        ];
    }
}
