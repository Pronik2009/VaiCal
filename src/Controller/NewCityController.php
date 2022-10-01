<?php /** @noinspection PhpUnusedAliasInspection */

namespace App\Controller;

use App\Entity\NewCity;
use App\Service\ValidatorService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewCityController extends AbstractController
{
    /**
     * @Route("api/cities/new", methods={"POST"})
     *
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @param ValidatorService $validator
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function postNewCity(Request $request, ManagerRegistry $managerRegistry, ValidatorService $validator): JsonResponse
    {
        $data = $request->toArray();

        $validation = $validator->validate($data, ValidatorService::NEW_CITY_ASSERT);
        if ($validation->count() !== 0) {
            return $this->json([
                'errors' => $validation,
            ], Response::HTTP_CONFLICT);
        }

        // save current session's info and data to entity NewCity, and return ID
        $newCity = new NewCity();
        $newCity->setName($data['name']);
        $newCity->setLatitude($data['lat']);
        $newCity->setLongitude($data['lon']);
        $newCity->setIP($request->getClientIp());
        $newCity->setUserAgent($request->headers->get('user-agent'));

        //Check if client try spam (sending often than 1 day)
        $em = $managerRegistry->getManager();
        if ($em->getRepository(NewCity::class)->checkSpam($newCity)) {
            return $this->json([
                'errors' => 'you already sent City last 24hours, please wait',
            ], Response::HTTP_FORBIDDEN);
        }

        $em->persist($newCity);
        $em->flush();

        return $this->json([
            'id' => $newCity->getId(),
            'name' => $newCity->getName(),
            'message' => 'accepted',
        ],
            Response::HTTP_ACCEPTED
        );
    }
}
