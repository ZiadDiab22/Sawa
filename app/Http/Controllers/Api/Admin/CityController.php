<?php
// app/Http/Controllers/Api/Admin/CityController.php
namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\CityService;

class CityController extends Controller
{
    public function __construct(
        protected CityService $cityService
    ) {}

    // POST /api/admin/cities
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'country_name' => 'required|string|max:255',
            'timezone'     => 'required|string|max:255',
            'currency'     => 'required|string|max:10',
        ]);

        $city = $this->cityService->createCity($data);

        return response()->json([
            'message' => 'City created successfully',
            'data'    => $city
        ], 201);
    }

    // PUT /api/admin/cities/{id}
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'country_name' => 'sometimes|string|max:255',
            'timezone'     => 'sometimes|string|max:255',
            'currency'     => 'sometimes|string|max:10',
        ]);

        $city = $this->cityService->updateCity($id, $data);

        return response()->json([
            'message' => 'City updated successfully',
            'data'    => $city
        ]);
    }

    // DELETE /api/admin/cities/{id}
    public function destroy(int $id)
    {
        $this->cityService->deleteCity($id);

        return response()->json([
            'message' => 'City deleted successfully'
        ]);
    }

      public function index()
    {
        return response()->json([
            'data' => $this->cityService->getCities()
        ]);
    }
}
