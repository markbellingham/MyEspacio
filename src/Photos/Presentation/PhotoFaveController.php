<?php

declare(strict_types=1);

namespace MyEspacio\Photos\Presentation;

use MyEspacio\Framework\BaseController;
use MyEspacio\Framework\DataSet;
use MyEspacio\Framework\Http\RequestHandlerInterface;
use MyEspacio\Framework\Http\ResponseData;
use MyEspacio\Framework\Routing\HttpMethod;
use MyEspacio\Framework\Routing\Route;
use MyEspacio\Photos\Domain\Entity\PhotoFave;
use MyEspacio\Photos\Domain\Repository\PhotoFaveRepositoryInterface;
use MyEspacio\Photos\Domain\Repository\PhotoRepositoryInterface;
use MyEspacio\User\Domain\User;
use MyEspacio\User\Infrastructure\MySql\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class PhotoFaveController extends BaseController
{
    public function __construct(
        private readonly PhotoRepositoryInterface $photoRepository,
        private readonly PhotoFaveRepositoryInterface $photoFaveRepository,
        private readonly RequestHandlerInterface $requestHandler,
        private readonly SessionInterface $session,
    ) {
    }

    #[Route('/photos/{uuid}/fave', HttpMethod::POST)]
    public function add(Request $request, DataSet $pathParameters): Response
    {
        $valid = $this->requestHandler->validate($request);
        if ($valid === false) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_METHOD_NOT_ALLOWED,
                )
            );
        }

        $user = $this->session->get('user');
        if ($user instanceof User === false) {
            $user = UserRepository::getAnonymousUser();
        }

        $uuid = $pathParameters->uuidNull('uuid');
        if ($uuid === null) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_BAD_REQUEST,
                    translationKey: 'photos.invalid_uuid',
                )
            );
        }

        $photo = $this->photoRepository->fetchByUuid($uuid);
        if ($photo === null) {
            return $this->requestHandler->sendResponse(
                new ResponseData(
                    statusCode: Response::HTTP_NOT_FOUND,
                    translationKey: 'photos.not_found',
                )
            );
        }

        $photoFave = new PhotoFave($photo, $user);
        $success = $this->photoFaveRepository->save($photoFave);

        return $this->requestHandler->sendResponse(
            new ResponseData(
                statusCode: $success ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR,
                translationKey: $success ? 'photos.fave_saved' : 'general.server_error',
            )
        );
    }
}
