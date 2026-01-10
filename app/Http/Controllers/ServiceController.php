<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{

    public function index_all()
    {
        $services = Service::with('user')->latest()->get();
        return response()->json(['data' => $services]);
    }
    
    /* =============================
       GET /freelancer/services
    ============================== */
    public function index()
    {
        $user = Auth::user();

        $services = Service::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn ($service) => $this->transform($service));

        return response()->json($services);
    }

    /* =============================
       POST /freelancer/services
    ============================== */
    public function store(Request $request)
    {
        $user = Auth::user();

        Log::info("Creating service for user ID: {$user->id}");
        Log::info('Request data: ' . json_encode($request->all()));

        if ($user->role !== 'freelancer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
        ]);
        Log::info('Validated data: ' . json_encode($data));
        

        $service = Service::create([
            ...$data,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        return response()->json($this->transform($service), 201);
    }

    /* =============================
       PUT /freelancer/services/{id}/status
    ============================== */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();

        $service = Service::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $data = $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $service->update([
            'status' => $data['status'],
        ]);

        return response()->json([
            'message' => 'Service status updated',
            'service' => $this->transform($service),
        ]);
    }

    /* =============================
       DELETE /freelancer/services/{id}
    ============================== */
    public function destroy($id)
    {
        $user = Auth::user();

        $service = Service::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully',
        ]);
    }

    /* =============================
       Transformer (Flutter match)
    ============================== */
    private function transform(Service $service): array
    {
        return [
            'id' => $service->id,
            'title' => $service->title,
            'description' => $service->description,
            'category' => $service->category,
            'price' => (string) $service->price, // Flutter expects String
            'status' => $service->status,
            'createdAt' => $service->created_at->toISOString(),
        ];
    }
}
