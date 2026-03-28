<?php

namespace App\Http\Controllers;

use App\Contracts\Services\TripParticipantsServiceInterface;
use App\Http\Requests\InviteTripParticipantRequest;
use App\Http\Requests\UpdateInviteStatusRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TripParticipantsController extends Controller
{
    public function __construct(
        private readonly TripParticipantsServiceInterface $tripParticipantsService
    ){}

    public function invite(InviteTripParticipantRequest $request): JsonResponse
    {
        $userId = $this->getUserId();

        $this->tripParticipantsService->inviteUser($request->all(), $userId);

        return response()->json([
            'message' => 'User invited.'
        ], Response::HTTP_CREATED);
    }

    public function getInvites(int $id): JsonResponse
    {
        $userId = $this->getUserId();

        $invites = $this->tripParticipantsService->getAllTripInvites($id, $userId);

        return response()->json($invites, Response::HTTP_OK);
    }

    public function updateInviteStatus(UpdateInviteStatusRequest $request): JsonResponse
    {
        $userId = $this->getUserId();

        $this->tripParticipantsService->updateInviteStatus($request->all(), $userId);

        return response()->json([
            'message' => 'Invite updated successfully.'
        ], Response::HTTP_ACCEPTED);
    }
}