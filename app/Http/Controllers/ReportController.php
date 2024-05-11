<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Intervention\Image\Facades\Image;
use App\Http\Resources\ReportResource;
use Illuminate\Support\Facades\Storage;
use App\Models\Image as ImageModel;
use Brick\Math\BigInteger;

class ReportController extends Controller
{
    public function index() : JsonResponse//add pagination
    {
        $reports = Report::all();
        return response()->json(ReportResource::collection($reports));
    }


    // handle images upload and store

    public function store(Request $request) : JsonResponse
    {
        //$this->authorize('create', Brand::class);

        $user = $request->user();
        if($user->role->title != 'salesman'){
            return response()->json(['message' => 'فقط مندوب المبيعات يستطيع انشاء تقرير'], 400);
        } 

        $data = $request->validate([
            'title' => 'required|string|max:100|min:2',
            'type' => 'required|in:retail,mall,pharmacy,supermarket',
            'size' => 'required|in:small,medium,big',
            'neighborhood' => 'required|string|max:50|min:2',
            'street' => 'required|string|max:50|min:2',
            'landline_number' => 'required|string|max:7|min:7',
            'mobile_number' => 'required|string|max:12|min:10',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'status' => 'nullable|in:slow,fair,fast',
            'issue_date' => 'required|date',
            'notes' => 'required|string|max:255|min:0',
            'images' => 'nullable|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
                
        
        $report = Report::create([
            'title' => $data['title'],
            'type' => $data['type'],
            'size' => $data['size'],
            'neighborhood' => $data['neighborhood'],
            'street' => $data['street'],
            'landline_number' => $data['landline_number'],
            'mobile_number' => $data['mobile_number'],
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
            'status' => $data['status'],
            'issue_date' => $data['issue_date'],
            'notes' => $data['notes'],
            'user_id' => $request->user()->id,
        ]);

        $this->uploadImages($request->file('images'), $data['title'], $report->id); 

        return response()->json(['message' => 'report created successfully'], 201);
    }

   

    public function show(string $id) : JsonResponse
    {
        $report = Report::findOrFail($id);
        return response()->json(new ReportResource($report));
    }

    

    // public function update(Request $request, string $id) : JsonResponse
    // {
    //     return response()->json(['message' => 'report updated successfully']);
    // }

 

    public function destroy(string $id) : JsonResponse 
    {
        // delete images files from server
        $this->authorize('delete', Report::class);
        Report::withTrashed()->findOrFail($id)->delete();
        return response()->json(null, 204);
    }



    public function search($query) : JsonResponse //to save bandwidth and memory, create a simpler resource 
    { 
        $reports =  ReportResource::collection(Report::search($query)->get());
        return response()->json($reports);
    }



    // todo: for all images , delete the old ones when updating 
    private function uploadImages(array $imageFiles, string $title, int $report_id): array
    {
        $uploadedImages = [];

        foreach ($imageFiles as $imageFile) {
            $imgData = Image::make($imageFile)->fit(720, 1280)->encode('jpg');
            $fileName = $title . '-' . uniqid() . '.jpg';
            Storage::put('public/report/' . $fileName, $imgData);

            $createdImage = ImageModel::create([
                'path' => 'storage/report/' . $fileName,
                'report_id' =>  $report_id,
            ]);

            $uploadedImages[] = $createdImage->id;
        }

        return $uploadedImages;
    }
}
