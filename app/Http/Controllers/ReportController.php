<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Intervention\Image\Facades\Image;
use App\Http\Resources\ReportResource;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index() : JsonResponse//add pagination
    {
        $reports = Report::all();
        return response()->json(ReportResource::collection($reports));
    }



    public function store(Request $request) : JsonResponse
    {
        //$this->authorize('create', Brand::class);

        $data = $request->validate([
            'title' => 'required|string|max:50|min:4',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
                
        
        $image = $this->uploadImage($request->file('image'), $data['title']); 
        
        Report::create([
            'title' => $data['title'],
            'image' => $image,
        ]);

        return response()->json(['message' => 'Brand created successfully'], 201);
    }

   

    public function show(string $id) : JsonResponse
    {
        $report = Report::findOrFail($id);
        return response()->json(new ReportResource($report));
    }

    

    public function update(Request $request, string $id) : JsonResponse
    {
        // $brand = Brand::findOrFail($id);

        // $this->authorize('update', $brand);

        // $data = $request->validate([
        //     'title' => 'nullable|string|max:50|min:4',
        //     'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        // ]);

        // $image = array_key_exists('image', $data) ? $this->uploadImage($request->file('image'), $data['title']) : null;
        
        // if($data['title']) $brand->title = $data['title']; 
        // if($image) $brand->image_id = $image->id; 
        // $brand->save();
    
        return response()->json(['message' => 'Brand updated successfully']);
    }

 

    public function destroy(string $id) : JsonResponse 
    {
        $this->authorize('delete', Report::class);
        Report::withTrashed()->findOrFail($id)->delete();
        return response()->json(null, 204);
    }



    public function search($query) : JsonResponse //to save bandwidth and memory, create a simpler resource 
    { 
        $reports =  ReportResource::collection(Report::search($query)->get());
        return response()->json($reports);
    }



    private function uploadImage($imageFile, string $title) : string
    { 
        $imgData = Image::make($imageFile)->fit(720, 1280)->encode('jpg'); // import
        $fileName = $title . '-' . uniqid() . '.jpg';
        Storage::put('public/brand/' . $fileName , $imgData);

        return 'storage/brand/' . $fileName;
            
    }
}
