<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Device;
use App\Service\ValidatorService;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DeviceController extends AbstractController
{
    private ObjectManager $em;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->em = $managerRegistry->getManager();
    }

    /**
     * @Route("api/devices/register", methods={"POST"})
     *
     * @param Request $request
     * @param ValidatorService $validator
     *
     * @return JsonResponse
     */
    public function registerNewDevice(Request $request, ValidatorService $validator): JsonResponse
    {
        $data = $request->toArray();

        $validation = $validator->validate($data, ValidatorService::NEW_DEVICE_ASSERT);
        if ($validation->count() !== 0) {
            return $this->json([
                'errors' => $validation,
            ], Response::HTTP_CONFLICT);
        }

        $city = $this->em->getRepository(City::class)->find($data['city']);
        if (!$city instanceof City) {
            return $this->json([
                'errors' => 'City not found',
            ], Response::HTTP_NOT_FOUND);
        }
        // save current session's info and data to entity Device, and return ID
        $newDevice = new Device();
        $newDevice->setModel($data['model']);
        $newDevice->setPlatform($data['platform']);
        $newDevice->setUuid($data['uuid']);
        $newDevice->setVersion($data['version']);
        $newDevice->setManufacturer($data['manufacturer']);
        $newDevice->setSerial($data['serial']);
        $newDevice->setFirebaseToken($data['firebaseToken']);
        $newDevice->setCity($city);
        $newDevice->setIP($request->getClientIp());
        $newDevice->setUserAgent($request->headers->get('user-agent'));

        $this->em->persist($newDevice);
        $this->em->flush();

        return $this->json([
            'id' => $newDevice->getId(),
            'name' => $newDevice->getUuid(),
            'message' => 'accepted',
        ],
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * @Route("api/devices/update/{id}", methods={"PATCH"})
     *
     * @param Request $request
     * @param Device $device
     * @param ValidatorService $validator
     *
     * @return JsonResponse
     */
    public function updateDevice(Request $request, Device $device, ValidatorService $validator): JsonResponse
    {
        $data = $request->toArray();

        $validation = $validator->validate($data, ValidatorService::UPDATE_DEVICE_ASSERT);
        if ($validation->count() !== 0) {
            return $this->json([
                'errors' => $validation,
            ], Response::HTTP_CONFLICT);
        }

        $city = $this->em->getRepository(City::class)->find($data['city']);
        if (!$city instanceof City) {
            return $this->json([
                'errors' => 'City not found',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($device->getUuid() !== $data['uuid']) {
            return $this->json([
                'errors' => 'Device not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // save current session's info and data to entity Device, and return ID
        $device->setFirebaseToken($data['firebaseToken']);
        $device->setCity($city);
        $device->setIP($request->getClientIp());
        $device->setUserAgent($request->headers->get('user-agent'));

        $this->em->persist($device);
        $this->em->flush();

        return $this->json([
            'id' => $device->getId(),
            'message' => 'updated',
        ],
            Response::HTTP_ACCEPTED
        );
    }

    /**
     * @Route("api/devices/check", methods={"POST"})
     *
     * @param Request $request
     * @param ManagerRegistry $managerRegistry
     * @param ValidatorService $validator
     *
     * @return JsonResponse
     */
    public function checkDevice(Request $request, ManagerRegistry $managerRegistry, ValidatorService $validator): JsonResponse
    {
        $data = $request->toArray();

        $validation = $validator->validate($data, ValidatorService::CHECK_DEVICE_ASSERT);
        if ($validation->count() !== 0) {
            return $this->json([
                'errors' => $validation,
            ], Response::HTTP_CONFLICT);
        }

        $device = $managerRegistry->getRepository(Device::class)->findOneBy(['uuid' => $data['uuid']]);
        if ($device instanceof Device) {
            return $this->json([
                'id' => $device->getId(),
                'message' => 'Device found',
            ],
                Response::HTTP_OK
            );
        }

        return $this->json([
            'message' => 'Device not found',
        ],
            Response::HTTP_NOT_FOUND
        );
    }
}
