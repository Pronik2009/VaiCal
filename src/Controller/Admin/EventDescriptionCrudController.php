<?php

namespace App\Controller\Admin;

use App\Entity\EventDescription;
use App\Entity\Language;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EventDescriptionCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }
    public static function getEntityFqcn(): string
    {
        return EventDescription::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('frontId'),
            AssociationField::new('language'),
            TextEditorField::new('description'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $cloneAction = Action::new('clone', 'Clone')
            ->linkToCrudAction('cloneEntity')
            ->setCssClass('btn btn-secondary');

        return $actions
            ->add(Crud::PAGE_INDEX, $cloneAction);
    }

    public function cloneEntity(AdminContext $context, EntityManagerInterface $entityManager): RedirectResponse
    {
        /** @var EventDescription $eventDescription */
        $eventDescription = $context->getEntity()->getInstance();
        $clonedEventDescription = clone $eventDescription;

        $existEventDescriptions = $entityManager->getRepository(EventDescription::class)->findBy(['frontId' => $eventDescription->getFrontId()]);
        $languages = $entityManager->getRepository(Language::class)->findAll();
        $isNewLanguageSet = false;
        foreach ($languages as $language) {
            foreach ($existEventDescriptions as $existEventDescription) {
                if ($existEventDescription->getLanguage()->getId() === $language->getId()) {
                    continue 2;
                }
            }

            $clonedEventDescription->setLanguage($language);
            $isNewLanguageSet = true;
            break;
        }
        if ($isNewLanguageSet === false) {
            $url = $this->adminUrlGenerator
                ->setAction(Crud::PAGE_INDEX)
                ->generateUrl();

            $this->addFlash('info', 'All languages are already used for this event description.');

            return $this->redirect($url);
        }

        $entityManager->persist($clonedEventDescription);
        $entityManager->flush();

        $url = $this->adminUrlGenerator
            ->setEntityId($clonedEventDescription->getId())
            ->setAction(Crud::PAGE_EDIT)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters
            ->add(TextFilter::new('frontId'))
            ->add(EntityFilter::new('language'))
        ;

        return parent::configureFilters($filters);
    }
}
