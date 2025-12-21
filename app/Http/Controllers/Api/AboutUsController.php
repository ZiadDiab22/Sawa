<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AboutUs\AboutUsService;

class AboutUsController extends Controller
{
    public function __construct(
        protected AboutUsService $service
    ) {}

    // GET /api/about-us
    public function show()
    {
        return response()->json(
            $this->service->get()
        );
    }

    // PUT /api/admin/about-us
    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string',

            'facebook_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'whatsapp_url' => 'nullable|url',

            'facebook_icon' => 'nullable|image|max:2048',
            'instagram_icon' => 'nullable|image|max:2048',
            'website_icon' => 'nullable|image|max:2048',
            'whatsapp_icon' => 'nullable|image|max:2048',
        ]);

        return response()->json([
            'message' => 'About Us updated successfully',
            'data' => $this->service->update($request->all())
        ]);
    }

    // POST /api/admin/about-us
public function store(Request $request)
{
    $request->validate([
        'company_name' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'description' => 'nullable|string',

        'facebook_url' => 'nullable|url',
        'instagram_url' => 'nullable|url',
        'website_url' => 'nullable|url',
        'whatsapp_url' => 'nullable|url',

        'facebook_icon' => 'nullable|image|max:2048',
        'instagram_icon' => 'nullable|image|max:2048',
        'website_icon' => 'nullable|image|max:2048',
        'whatsapp_icon' => 'nullable|image|max:2048',
    ]);

    return response()->json([
        'message' => 'About Us created successfully',
        'data' => $this->service->create($request->all())
    ], 201);
}

}
