<?php

namespace App\Services;

use App\Contracts\Repositories\TripParticipantsRepositoryInterface;
use App\Contracts\Services\TripParticipantsServiceInterface;
use App\Contracts\Services\TripServiceInterface;
use App\DTO\TripParticipants\GetAllTripParticipantsDto;
use App\DTO\TripParticipants\InviteUserDto;
use App\DTO\TripParticipants\UpdateInviteDto;
use App\Exceptions\CannotInviteYourselfException;
use App\Exceptions\InvalidUserTripInvite;
use App\Exceptions\TripInvitationNotFound;
use App\Exceptions\TripInviteAlreadySent;
use App\Exceptions\TripInviteStatusAlreadyUpdated;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

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
            throw new CannotInviteYourselfException("Creator can't invite himself for a trip.", Response::HTTP_BAD_REQUEST);
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

    public function updateInviteStatus(array $inviteData, int $userId): void
    {
        $tripInvite = $this->tripParticipantsRepository->getInvite($inviteData['invite_id']);

        if($tripInvite === null)
        {
            throw new TripInvitationNotFound("Trip invitation doesn't exist.", Response::HTTP_NOT_FOUND);
        }

        if($userId !== $tripInvite->user_id)
        {
            throw new InvalidUserTripInvite("User wasn't invited for the trip.", Response::HTTP_FORBIDDEN);
        }
        
        if($tripInvite->status !== 'pending')
        {
            throw new TripInviteStatusAlreadyUpdated("Trip is not pending and can't be accepted/refuse.", Response::HTTP_BAD_REQUEST);
        }

        $tripUpdateDto = UpdateInviteDto::from([
            'status' => $inviteData['status']
        ]);

        $this->tripParticipantsRepository->updateInvite($tripUpdateDto, $inviteData['invite_id']);
    }

}