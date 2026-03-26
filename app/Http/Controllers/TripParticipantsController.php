<?php

namespace App\Http\Controllers;

use App\Contracts\Services\TripParticipantsServiceInterface;
use App\Http\Requests\InviteTripParticipantRequest;
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
}