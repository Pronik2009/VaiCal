<?php

namespace App\Controller\Admin;

use App\Entity\City;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

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
        $zone = IntegerField::new('zone');

        if ($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) {
            return [
                $name, $slug, $zone,
            ];
        }

        return [
            $id, $name, $slug, $years, $zone,
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters
            ->add(TextFilter::new('name'))
            ->add(TextFilter::new('slug'))
            ->add(NumericFilter::new('zone'))
        ;

        return parent::configureFilters($filters);
    }
}
