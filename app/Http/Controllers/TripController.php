<?php

namespace App\Http\Controllers;

use App\Contracts\Services\TripServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\UpdateTripRequest;
use Symfony\Component\HttpFoundation\Response;

class TripController extends Controller
{
    public function __construct(
        private readonly TripServiceInterface $tripService
    ){}

    public function createTrip(CreateTripRequest $request)
    {
        $userId = $this->getUserId();

        $this->tripService->createTrip($request->all(), $userId);

        return response()->json([
            'message' => 'Trip created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function getUserTrips()
    {
        $userId = $this->getUserId();
        $trips = $this->tripService->getUserTrips($userId);

        return response()->json($trips, Response::HTTP_OK);
    }

    public function getUserTrip(int $tripId)
    {
        $userId = $this->getUserId();

        return response()->json(
            $this->tripService->getUserTrip($tripId, $userId),
            Response::HTTP_OK
        );
    }

    public function updateTrip(UpdateTripRequest $request, int $tripId)
    {
        $userId = $this->getUserId();

        $this->tripService->updateTrip($request->all(), $tripId, $userId);

        return response()->json([
            'message' => 'Trip updated successfully.'
        ], Response::HTTP_ACCEPTED);
    }

    public function deleteTrip(int $tripId)
    {
        $userId = $this->getUserId();

        $this->tripService->deleteTrip($tripId, $userId);

        return response()->noContent();
    }

    private function getUserId(): int
    {
        return auth()->guard('api')->user()->id;
    }
}