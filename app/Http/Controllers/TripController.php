<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Repositories\Interfaces\TripRepository as TripRepositoryContract;
use App\Repositories\Interfaces\VehicleRepository as VehicleRepositoryContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TripController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TripRepositoryContract $tripRepository,
        protected VehicleRepositoryContract $vehicleRepository,
    ) {}

    /**
     * Display a listing of the trips.
     */
    public function index(): View
    {
        $trips = Trip::with('vehicle')->get();

        return view('trips.index')
            ->with('trips', $trips);
    }

    /**
     * Show the form for creating a new trip.
     */
    public function create(): View
    {
        // Get all vehicles for the current company
        $vehicles = $this->vehicleRepository->getAllForCompany(auth()->user()->current_company_id);

        // Get the first vehicle to use as default
        $vehicle = $vehicles->first();

        if (! $vehicle) {
            // If no vehicles are available, redirect to vehicles index with an error
            return redirect()->route('vehicles.index')
                ->with('error', 'You need to create a vehicle first before adding a trip.');
        }

        return view('trips.create')
            ->with('vehicle', $vehicle)
            ->with('vehicles', $vehicles);
    }

    /**
     * Store a newly created trip in storage.
     */
    public function store(CreateTripRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Get the vehicle
        $vehicle = $this->vehicleRepository->find($data['vehicle_id']);

        if (! $vehicle) {
            return redirect()->back()->withErrors(['vehicle_id' => 'Vehicle not found.'])->withInput();
        }

        // Calculate distance if not provided
        if (empty($data['distance']) && isset($data['start_odometer']) && isset($data['end_odometer'])) {
            $data['distance'] = $data['end_odometer'] - $data['start_odometer'];
        }

        $trip = $this->tripRepository->create($data);

        return redirect()->route('trips.show', $trip)
            ->with('success', 'Trip created successfully.');
    }

    /**
     * Display the specified trip.
     */
    public function show(Trip $trip): View
    {
        $vehicle = $trip->vehicle;

        return view('trips.show')
            ->with('trip', $trip)
            ->with('vehicle', $vehicle);
    }

    /**
     * Show the form for editing the specified trip.
     */
    public function edit(Trip $trip): View
    {
        $vehicle = $trip->vehicle;
        $vehicles = $this->vehicleRepository->getAllForCompany(auth()->user()->current_company_id);

        return view('trips.edit', [
            'trip' => $trip,
            'vehicle' => $vehicle,
            'vehicles' => $vehicles,
        ]);
    }

    /**
     * Update the specified trip in storage.
     */
    public function update(UpdateTripRequest $request, Trip $trip): RedirectResponse
    {
        $data = $request->validated();

        // Recalculate distance if odometer readings changed
        if (isset($data['start_odometer']) && isset($data['end_odometer'])) {
            $data['distance'] = $data['end_odometer'] - $data['start_odometer'];
        }

        $this->tripRepository->update($trip, $data);

        return redirect()->route('trips.show', $trip)
            ->with('success', 'Trip updated successfully.');
    }

    /**
     * Remove the specified trip from storage.
     */
    public function destroy(Trip $trip): RedirectResponse
    {
        $this->tripRepository->delete($trip);

        return redirect()->route('trips.index')
            ->with('success', 'Trip deleted successfully.');
    }
}
