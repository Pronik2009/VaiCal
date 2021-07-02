<?php

namespace App\Controller\Admin;

use App\Entity\NewCity;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class NewCityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NewCity::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->disable(Action::EDIT, Action::NEW);

        return parent::configureActions($actions);
    }
}
