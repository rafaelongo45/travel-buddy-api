<?php

namespace App\Http\Controllers;

use App\Contracts\Services\TripServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTripRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TripController extends Controller
{
    public function __construct(
        private readonly TripServiceInterface $tripService
    ){}

    public function createTrip(CreateTripRequest $request)
    {
        $userId = auth()->guard('api')->user()->id;

        $this->tripService->createTrip($request->all(), $userId);

        return response()->json([
            'message' => 'Trip created successfully.'
        ], Response::HTTP_CREATED);
    }

    public function getUserTrips()
    {
        $userId = auth()->guard('api')->user()->id;
        $trips = $this->tripService->getUserTrips($userId);

        return response()->json($trips, Response::HTTP_OK);
    }

    public function getUserTrip(int $id)
    {
        $userId = auth()->guard('api')->user()->id;

        return response()->json(
            $this->tripService->getUserTrip($id, $userId),
            Response::HTTP_OK
        );
    }
}