<?php
// app/Http/Controllers/Api/Admin/VehicleTypeController.php
namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\VehicleTypeService;

class VehicleTypeController extends Controller
{
    public function __construct(
        protected VehicleTypeService $vehicleTypeService
    ) {}
public function store(Request $request)
{
    $data = $request->validate([
        'name'          => 'required|string|max:255',
        'image'         => 'nullable|image|max:2048',
        'base_fare'     => 'required|numeric|min:0',
        'per_km'        => 'required|numeric|min:0',
        'per_minute'    => 'required|numeric|min:0',
        'minimum_fare'  => 'required|numeric|min:0',
    ]);

    $vehicleType = $this->vehicleTypeService->createVehicleType($data);

    return response()->json([
        'message' => 'Vehicle type created successfully',
        'data'    => $vehicleType
    ], 201);
}


    // PUT /api/admin/vehicle-types/{id}
   public function update(Request $request, int $id)
{
    $data = $request->validate([
        'name'          => 'sometimes|string|max:255',
        'image'         => 'sometimes|image|max:2048',
        'base_fare'     => 'sometimes|numeric|min:0',
        'per_km'        => 'sometimes|numeric|min:0',
        'per_minute'    => 'sometimes|numeric|min:0',
        'minimum_fare'  => 'sometimes|numeric|min:0',
    ]);

    $vehicleType = $this->vehicleTypeService->updateVehicleType($id, $data);

    return response()->json([
        'message' => 'Vehicle type updated successfully',
        'data'    => $vehicleType
    ]);
}


    // DELETE /api/admin/vehicle-types/{id}
    public function destroy(int $id)
    {
        $this->vehicleTypeService->deleteVehicleType($id);

        return response()->json([
            'message' => 'Vehicle type deleted successfully'
        ]);
    }

     public function index()
    {
        return response()->json([
            'data' => $this->vehicleTypeService->getVehicleTypes()
        ]);
    }
}
