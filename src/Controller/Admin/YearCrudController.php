<?php

namespace App\Controller\Admin;

use App\Entity\Year;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;

class YearCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Year::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $city = AssociationField::new('city');
        $year = IntegerField::new('value');
        $jan = ArrayField::new('janstr', 'January');
        $feb = ArrayField::new('febstr', 'February');
        $mar = ArrayField::new('marstr', 'March');
        $apr = ArrayField::new('aprstr', 'April');
        $may = ArrayField::new('maystr', 'May');
        $jun = ArrayField::new('junstr', 'June');
        $jul = ArrayField::new('julstr', 'July');
        $aug = ArrayField::new('augstr', 'August');
        $sem = ArrayField::new('semstr', 'September');
        $oct = ArrayField::new('octstr', 'October');
        $nov = ArrayField::new('novstr', 'November');
        $dem = ArrayField::new('demstr', 'December');

        return [
            $id, $city, $year,
            $jan, $feb, $mar,
            $apr, $may, $jun,
            $jul, $aug, $sem,
            $oct, $nov, $dem,
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->disable(Action::EDIT, Action::NEW);

        return parent::configureActions($actions);
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters
            ->add(EntityFilter::new('city'))
            ->add(NumericFilter::new('value', 'Year'))
        ;

        return parent::configureFilters($filters);
    }
}
