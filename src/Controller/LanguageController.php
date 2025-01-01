<?php

namespace App\Controller;

use App\Entity\Language;
use App\Service\ValidatorService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    /**
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @param ValidatorService $validator
     *
     * @return JsonResponse
     */
    #[Route('api/languages/check', methods: ['GET'])]
    public function checkLanguage(Request $request, ManagerRegistry $managerRegistry, ValidatorService $validator): JsonResponse
    {
        $shortName = $request->query->get('shortName');

        $validation = $validator->validate(['shortName' => $shortName], ValidatorService::CHECK_LANGUAGE_ASSERT);
        if ($validation->count() !== 0) {
            return $this->json([
                'errors' => $validation,
            ], Response::HTTP_CONFLICT);
        }

        $shortName = mb_strcut($shortName, 0, 2);

        $language = $managerRegistry->getRepository(Language::class)->findOneBy(['shortName' => $shortName . '-**']);
        if ($language instanceof Language) {
            return $this->json([
                'id' => $language->getId(),
                'message' => 'Language found',
            ],
                Response::HTTP_OK
            );
        }

        return $this->json([
            'message' => false,
        ],
            Response::HTTP_NOT_FOUND
        );
    }
}
