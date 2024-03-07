<?php

namespace App\Controller\Admin;

use App\Entity\City;
use App\Entity\Device;
use App\Entity\Year;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Kreait\Firebase\Factory;

class DeviceCrudController extends AbstractCrudController
{
    private function factory(): Factory {
        return (new Factory)->withServiceAccount( dirname( __FILE__, 4 ) . '/config/VaiCal_credentials.json' );
    }
    
    private EntityManager $database;

    public function __construct( EntityManagerInterface $database )
    {
        $this->database = $database;
    }

    public static function getEntityFqcn(): string
    {
        return Device::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('model'),
            TextField::new('platform'),
            TextField::new('uuid'),
            TextField::new('version'),
            TextField::new('manufacturer'),
            TextField::new('serial'),
            AssociationField::new('city'),
            BooleanField::new('notification'),
            IntegerField::new('notifyDay'),
            TextField::new('notifyTime'),
            TextField::new('UserAgent'),
            TextField::new('IP'),
            TextField::new('firebaseToken')->onlyOnForms(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewInvoice = Action::new('get-FB-auth', 'get FB auth', 'fa fa-file-invoice')
            ->linkToCrudAction('renderAuth')
            ->createAsGlobalAction();

        $actions
            ->add(Crud::PAGE_INDEX, $viewInvoice);

        return parent::configureActions($actions);
    }

    public function renderAuth( AdminContext $context ): RedirectResponse
    {
        $devices = $this->database->getRepository( Device::class )->findAll();
        $factory = $this->factory();
        $messaging = $factory->createMessaging();

        foreach ( $devices as $device ) { 
            $fireBaseToken = $device->getFirebaseToken();
            $result = $messaging->validateRegistrationTokens( $fireBaseToken );

            if ( empty( $result[ 'valid' ] ) ) {
                $id = $device->getId();
                mail( 'damodara16108@gmail.com', 'Invalid Token', $id );
            }

        }
        
        dump('End script');

        return new RedirectResponse( 'dumped', 302, [] );
    }
    
}
