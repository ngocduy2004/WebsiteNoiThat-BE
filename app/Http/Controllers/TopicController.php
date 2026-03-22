<?php

namespace App\Http\Controllers;
use App\Models\Topic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $query = Topic::query();


        // Tìm kiếm theo tên hoặc mã
        if ($request->has('search') && $request->search != "") {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        $total = Topic::count();


        if ($request->has('limit') && $request->has('page')) {
            $limit = $request->input('limit');
            $page = $request->input('page');
            $offset = ($page - 1) * $limit;

            $query->offset($offset)->limit($limit);
        } else {

            if ($request->has('limit')) {
                $limit = $request->limit;
                $query->limit($limit);
            }
        }



        $topic = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data' => $topic,
            'total' => $total,
            'message' => 'Lấy danh sách chủ đề thành công',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $topic = Topic::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'sort_order' => $request->sort_order ?? 0,
            'description' => $request->description,
            'status' => $request->status,
            'created_at' => now(),
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tạo chủ đề thành công',
            'data' => $topic,
        ], 200);
    }

    // =========================
    // SHOW
    // =========================
    public function show($id)
    {
        $topic = Topic::with('posts')->find($id);

        if (!$topic) {
            return response()->json([
                'status' => false,
                'message' => 'Chủ đề không tồn tại',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $topic,
        ], 200);
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $topic = Topic::find($id);

        if (!$topic) {
            return response()->json([
                'status' => false,
                'message' => 'Chủ đề không tồn tại',
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string',
            'status' => 'required|integer',
        ]);

        $topic->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'sort_order' => $request->sort_order ?? $topic->sort_order,
            'description' => $request->description,
            'status' => $request->status,
            'updated_at' => now(),
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật chủ đề thành công',
        ], 200);
    }


    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $topic = Topic::find($id);

        if (!$topic) {
            return response()->json([
                'status' => false,
                'message' => 'Chủ đề không tồn tại',
            ], 404);
        }

        $topic->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa chủ đề thành công',
        ], 200);
    }


}