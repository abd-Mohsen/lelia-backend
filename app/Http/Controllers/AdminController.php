<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    //TODO policy to let just admin do it
    public function getRoleCounts(Request $request): JsonResponse
    {
        $roleCounts = User::with('role')
            ->select('role_id')
            ->groupBy('role_id')
            ->selectRaw('count(*) as count')
            ->get()
            ->mapWithKeys(function ($item) {
                $roleTitle = $item->role->title;
                return [$roleTitle => $item->count];
            });

        return response()->json($roleCounts);
    }

    public function topSalesmen(Request $request): JsonResponse
    {
        $topSalesmen = User::withCount('reports')
            ->whereHas('role', function($query) {
                $query->where('title', 'salesman'); 
            })
            ->orderBy('reports_count', 'desc')
            ->take(7)
            ->get();

        return response()->json($topSalesmen);
    }
}
