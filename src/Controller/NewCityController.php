<?php

namespace App\Controller;

use App\Entity\NewCity;
use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class NewCityController extends AbstractController
{
    /**
     * @Route("api/cities/new", methods={"POST"})
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws NonUniqueResultException
     */
    public function postNewCity(Request $request): JsonResponse
    {
        $data = $request->toArray();

        $validator = Validation::createValidator();
        $constraints = new Assert\Collection([
            'name' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 2,
                    'max' => 40,
                    'minMessage' => "Name must be at least {{ limit }} characters long",
                    'maxMessage' => "Name cannot be longer than {{ limit }} characters",
                ]),
            ],
            'lat' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 7,
                    'max' => 18,
                    'minMessage' => "Latitude must be at least {{ limit }} characters long",
                    'maxMessage' => "Latitude cannot be longer than {{ limit }} characters",
                ]),
                new Assert\Regex('/^(\-?\d+(\.\d+)?)+$/', 'Coords can contain only numbers, "." and "-"'),
            ],
            'lon' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 7,
                    'max' => 18,
                    'minMessage' => "Longitude must be at least {{ limit }} characters long",
                    'maxMessage' => "Longitude cannot be longer than {{ limit }} characters",
                ]),
                new Assert\Regex('/^(\-?\d+(\.\d+)?)+$/', 'Coords can contain only numbers, "." and "-"'),
            ],
            'token' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 32,
                    'max' => 32,
                ]),
                new Assert\Callback([__CLASS__,"validateSecurityHash"]),
            ],
        ]);

        $validation = $validator->validate($data, $constraints);
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
        $em = $this->getDoctrine()->getManager();
        if($em->getRepository(NewCity::class)->checkSpam($newCity)){
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

    public static function validateSecurityHash($object, ExecutionContextInterface $context, $payload): void
    {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $key1 = $date->format('YmdH');
        $key2 = $date->format('i');
        if((int)$key2[1]<9){
            $key2[1]='0';
        } else {
            $date->add(new DateInterval('PT1M'));
            $key1 = $date->format('YmdH');
            $key2 = $date->format('i');
        }
        $hash = md5($_ENV['SECRET_PHRASE'] . $key1 . $key2);

        if ($object !== $hash) {
            $context->buildViolation('Token is invalid, get out!')
                ->addViolation();
        }
    }
}
