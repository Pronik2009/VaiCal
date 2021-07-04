<?php

namespace App\Controller;

use App\Entity\City;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use ErrorException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File;

class YearController extends AbstractController
{
    public const TempFileName = 'uploaded_city.txt';
    public $projectDir;

    public function __construct(KernelInterface $kernel)
    {
        $this->projectDir = $kernel->getContainer()->getParameter('kernel.project_dir');
    }

    /**
     * @Route("/year", name="year_index")
     *
     * @param EntityManagerInterface $em
     *
     * @return Response
     *
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(EntityManagerInterface $em): Response
    {
        $cities = $em->getRepository(City::class)->getAllNames();

        return $this->render('year/index.html.twig', [
            'controller_name' => 'YearController',
            'cities' => $cities,
        ]);
    }

    /**
     * @Route("/year/upload/{format}", defaults={"format"=null}, name="year_upload")
     *
     * @param Request $request
     * @ParamConverter("format")
     * @param string|null $format
     * @param ImportService $importService
     * @param AdminUrlGenerator $adminUrlGenerator
     *
     * @return RedirectResponse
     *
     * @IsGranted("ROLE_ADMIN")
    **/
    public function import(Request $request, ImportService $importService, AdminUrlGenerator $adminUrlGenerator, string $format = null): RedirectResponse
    {
        $directory = $this->projectDir;
        /** @var File\UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('upload');
        $message = 'File "' . $uploadedFile->getClientOriginalName() . '" ';

        try {
            $tmpFilePath = $directory .'/'. self::TempFileName;
            $uploadedFile->move($directory, self::TempFileName);
            $file = file($tmpFilePath, FILE_IGNORE_NEW_LINES);
            $this->removeTmp($tmpFilePath);
            if ($file) {
                $importService->parseAndSave($file, $request->get('city'), $format);
                $this->addFlash('success', $message . 'was uploaded!');
            } else {
                $this->addFlash('warning', $message . 'wrong format!');
            }
        } catch (ErrorException $e) {
            $this->removeTmp($tmpFilePath);
            $this->addFlash('warning', $message . 'was not uploaded!<br>' . $e->getMessage());
        }

        $url = $adminUrlGenerator
            ->set('routeName', 'year_index')
            ->set('menuIndex', 5)
            ->set('submenuIndex', 1)
            ->generateUrl();

        return $this->redirect($url);
    }

    /**
     * @param $filePath
     */
    private function removeTmp($filePath): void
    {
        $filesystem = new Filesystem();
        try {
            $filesystem->remove($filePath);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while clearing temporary file: " . $exception->getPath();
        }
    }
}
