<?php

namespace App\Services;

use App\Contracts\Repositories\TripParticipantsRepositoryInterface;
use App\Contracts\Services\TripParticipantsServiceInterface;
use App\Contracts\Services\TripServiceInterface;
use App\DTO\TripParticipants\GetAllTripParticipantsDto;
use App\DTO\TripParticipants\InviteUserDto;
use App\Exceptions\TripInviteAlreadySent;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TripParticipantsService implements TripParticipantsServiceInterface
{
    public function __construct(
        private readonly TripServiceInterface $tripService,
        private readonly TripParticipantsRepositoryInterface $tripParticipantsRepository
    ){}

    public function inviteUser(array $inviteData, int $userId): void
    {
        $tripParticipantDto = InviteUserDto::from($inviteData);
 
        $this->tripService->getUserTrip($tripParticipantDto->trip_id, $userId);
               
        if($userId === $tripParticipantDto->user_id) {
            throw new BadRequestHttpException("Creator can't invite himself for a trip.");
        }

        $tripInvite = $this->tripParticipantsRepository->findInvite($tripParticipantDto->user_id, $tripParticipantDto->trip_id);

        if($tripInvite !== null) {
            throw new TripInviteAlreadySent('The invite was already sent for the user.', Response::HTTP_CONFLICT);
        }

        $this->tripParticipantsRepository->create($tripParticipantDto);
    }

    public function getAllTripInvites(int $tripId, int $userId): Collection
    {
        $this->tripService->getUserTrip($tripId, $userId);
        
        $tripParticipantsData = $this->tripParticipantsRepository->getInvites($tripId);

        return GetAllTripParticipantsDto::collect($tripParticipantsData);
    }

}