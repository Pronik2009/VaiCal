<?php

namespace App\Controller\Admin;

use App\Entity\Device;
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
use Kreait\Firebase\Auth\SignIn\FailedToSignIn;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\AuthException;
use Kreait\Firebase\Exception\FirebaseException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Kreait\Firebase\Contract\Auth;

class DeviceCrudController extends AbstractCrudController
{
    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
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

    /**
     * @throws FirebaseException
     * @throws AuthException
     */
    public function renderAuth(AdminContext $context): RedirectResponse
    {
        $entity = $context->getEntity()->getInstance();

        $uid = 'some-uid';
        $customToken = $this->auth->createCustomToken($uid);

        // Get Custom token
        $idTokenString = $customToken->toString();

        // Verify Custom token
        $verifiedIdToken = null;
        $verifyErrorMessage = '';
        $signInErrorMessage = '';
        $user = '';
        $extractedUid = '';
        $asTokenResponse = '';
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idTokenString);
        } catch (FailedToVerifyToken $e) {
            $verifyErrorMessage = $e->getMessage();
        }
        if ($verifiedIdToken) {
            $extractedUid = $verifiedIdToken->claims()->get('sub');
            $user = $this->auth->getUser($uid);
        }
        // Sign in
        try {
            $signInResult = $this->auth->signInWithCustomToken($customToken);
            $asTokenResponse = $signInResult->asTokenResponse();
        } catch (FailedToSignIn $e) {
            $signInErrorMessage = $e->getMessage();
        }

        dd(
            $customToken->toString(),
            $verifyErrorMessage,
            $extractedUid,
            $user,
            $signInErrorMessage,
            $asTokenResponse,
        );

        return new RedirectResponse('dumped', 302, []);
    }
}
