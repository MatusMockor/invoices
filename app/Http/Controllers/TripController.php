<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Repositories\Interfaces\TripRepository as TripRepositoryContract;
use App\Repositories\Interfaces\VehicleRepository as VehicleRepositoryContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TripController extends Controller
{
    public function __construct(
        private TripRepositoryContract $tripRepository,
        private VehicleRepositoryContract $vehicleRepository,
    ) {
    }

    /**
     * Show the form for creating a new trip directly (without pre-selected vehicle).
     */
    public function createDirect(): View
    {
        // Get all vehicles for the current company
        $vehicles = $this->vehicleRepository->getAllForCompany(auth()->user()->current_company_id);

        return view('trips.create_direct', compact('vehicles'));
    }

    /**
     * Store a newly created trip in storage (from direct creation).
     */
    public function storeDirect(CreateTripRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Get the vehicle
        $vehicle = $this->vehicleRepository->find($data['vehicle_id']);

        if (!$vehicle) {
            return redirect()->back()->withErrors(['vehicle_id' => 'Vehicle not found.'])->withInput();
        }

        // Calculate distance if not provided
        if (empty($data['distance']) && isset($data['start_odometer']) && isset($data['end_odometer'])) {
            $data['distance'] = $data['end_odometer'] - $data['start_odometer'];
        }

        $trip = $this->tripRepository->create($data);

        return redirect()->route('vehicles.trips.show', [$vehicle, $trip])
            ->with('success', 'Trip created successfully.');
    }

    /**
     * Display a listing of the trips for a vehicle.
     */
    public function index(Vehicle $vehicle): View
    {
        $trips = $this->tripRepository->getAllForVehicle($vehicle->id);

        return view('trips.index', compact('vehicle', 'trips'));
    }

    /**
     * Show the form for creating a new trip.
     */
    public function create(Vehicle $vehicle): View
    {
        return view('trips.create', compact('vehicle'));
    }

    /**
     * Store a newly created trip in storage.
     */
    public function store(CreateTripRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validated();
        $data['vehicle_id'] = $vehicle->id;

        // Calculate distance if not provided
        if (empty($data['distance']) && isset($data['start_odometer']) && isset($data['end_odometer'])) {
            $data['distance'] = $data['end_odometer'] - $data['start_odometer'];
        }

        $trip = $this->tripRepository->create($data);

        return redirect()->route('vehicles.trips.show', [$vehicle, $trip])
            ->with('success', 'Trip created successfully.');
    }

    /**
     * Display the specified trip.
     */
    public function show(Vehicle $vehicle, Trip $trip): View
    {
        return view('trips.show', compact('vehicle', 'trip'));
    }

    /**
     * Show the form for editing the specified trip.
     */
    public function edit(Vehicle $vehicle, Trip $trip): View
    {
        return view('trips.edit', compact('vehicle', 'trip'));
    }

    /**
     * Update the specified trip in storage.
     */
    public function update(UpdateTripRequest $request, Vehicle $vehicle, Trip $trip): RedirectResponse
    {
        $data = $request->validated();

        // Recalculate distance if odometer readings changed
        if (isset($data['start_odometer']) && isset($data['end_odometer'])) {
            $data['distance'] = $data['end_odometer'] - $data['start_odometer'];
        }

        $this->tripRepository->update($trip, $data);

        return redirect()->route('vehicles.trips.show', [$vehicle, $trip])
            ->with('success', 'Trip updated successfully.');
    }

    /**
     * Remove the specified trip from storage.
     */
    public function destroy(Vehicle $vehicle, Trip $trip): RedirectResponse
    {
        $this->tripRepository->delete($trip);

        return redirect()->route('vehicles.trips.index', $vehicle)
            ->with('success', 'Trip deleted successfully.');
    }
}
