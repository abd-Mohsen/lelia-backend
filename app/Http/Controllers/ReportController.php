<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Image as ImageModel;
use Intervention\Image\Facades\Image;
use App\Http\Resources\ReportResource;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    // get all the reports of a logged in salesman
    public function index(Request $request) : JsonResponse
    {
        $this->authorize('viewMine', Report::class);
        $limit = $request->input('limit', 10);
        $reports = $request->user()->reports()->orderBy('created_at', 'desc')->paginate($limit);
        return response()->json(ReportResource::collection($reports));
    }



    // get the reports of all a supervisor subs
    public function mySubsReports(Request $request) : JsonResponse
    {
        $this->authorize('viewAny', Report::class);
        $limit = $request->input('limit', 10);
        $reports = $request->user()->supervisorAllReports()->orderBy('created_at', 'desc')->paginate($limit);
        return response()->json(ReportResource::collection($reports));
    }



    public function store(Request $request) : JsonResponse
    {
        $this->authorize('create', Report::class);

        //TODO put this in policy
        $user = $request->user();
        if($user->role->title != 'salesman'){
            return response()->json(['message' => 'فقط مندوب المبيعات يمكنه إنشاء تقرير'], 400);
        } 

        $data = $request->validate([
            'title' => 'required|string|max:100|min:2',
            'type' => 'required|in:retail,mall,pharmacy,supermarket',
            'size' => 'required|in:small,medium,big',
            'neighborhood' => 'required|string|max:50|min:2',
            'street' => 'required|string|max:50|min:2',
            'landline_number' => 'required|string|max:7|min:7',
            'mobile_number' => 'required|string|max:12|min:10',
            'longitude' => 'required|string',
            'latitude' => 'required|string',
            'status' => 'required|in:slow,fair,fast,unavailable',
            'issue_date' => 'required|date',
            'notes' => 'nullable|string|max:255|min:0',
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
            'longitude' => floatval($data['longitude']),
            'latitude' => floatval($data['latitude']),
            'status' => $data['status'] == 'unavailable' ? null : $data['status'], // did that because i cant upload null from dart http package(multipart file)
            'issue_date' => $data['issue_date'],
            'notes' => $data['notes'],
            'user_id' => $request->user()->id,
        ]);

        $this->uploadImages($request->file('images'), $data['title'], $report->id); 

        return response()->json(['message' => 'report created successfully'], 201);
    }

   

    // note: this return the reports of the user with id, not the report with id
    //TODO add pagination
    public function show(string $id, Request $request) : JsonResponse
    {
        $user = $request->user();
        $salesman = User::findOrFail($id);
        $this->authorize('view', Report::class);
        if ($salesman->role->title != 'salesman' or $salesman->supervisor->id != $user->id) {
            return response()->json(['message' => 'الموظف ليس مندوباً لديك'], 400);
        }
        $limit = $request->input('limit', 10);
        $reports = $salesman->reports()->orderBy('created_at', 'desc')->paginate($limit);
        return response()->json(ReportResource::collection($reports));
    }
    

    

    public function destroy(string $id) : JsonResponse 
    {
        $report = Report::findOrFail($id);

        $this->authorize('delete', $report);

        foreach ($report->images as $imageFile) {
            $fileName = str_replace('storage/report/', '', $imageFile->path);
            Storage::delete('public/report/' . $fileName);
            $imageFile->delete();
        }

        $report->delete();

        return response()->json(null, 204);
    }



    //TODO add pagination
    public function search($query) : JsonResponse 
    { 
        $reports =  ReportResource::collection(Report::search($query)->get());
        return response()->json($reports);
    }



    // TODO for all images , delete the old ones when updating 
    private function uploadImages(array $imageFiles, string $title, int $report_id): array
    {
        $uploadedImages = [];

        foreach ($imageFiles as $imageFile) {
            $imgData = Image::make($imageFile)->fit(1280, 720)->encode('jpg');
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

    public function exportReports(Request $request): JsonResponse
    {
        //TODO: allow only if admin, or if supervisor and the salesman belongs to him
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id', // Ensure user_id is valid if provided
        ]);

        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $userId = $data['user_id'];

        // Query to get reports based on the provided parameters
        $query = Report::whereBetween('created_at', [$startDate, $endDate]);

        // Filter by user_id if provided
        if ($userId) $query->where('user_id', $userId);

        // Execute the query and get the results
        $reports = $query->get();

        return response()->json(ReportResource::collection($reports));
    }
}
