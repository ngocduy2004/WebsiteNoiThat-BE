<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    // =========================
    // INDEX
    // =========================
    public function index(Request $request)
    {
        $query = Menu::query();

        // SEARCH
        if ($request->has('search') && $request->search != "") {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // PAGINATION
        if ($request->has('limit') && $request->has('page')) {
            $limit = $request->limit;
            $page = $request->page;
            $offset = ($page - 1) * $limit;
            $query->offset($offset)->limit($limit);
        } elseif ($request->has('limit')) {
            $query->limit($request->limit);
        }

        $query->orderBy('created_at', 'desc');

        $menu = $query->get();
        $total = Menu::count();

        return response()->json([
            'status' => true,
            'data' => $menu,
            'total' => $total,
            'message' => 'Lấy danh sách menu thành công',
        ], 200);
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'required|string|max:255',
            'position' => 'required|string|in:mainmenu,footermenu',
            'status' => 'required|integer|in:0,1',
        ]);

        $menu = Menu::create([
            'name' => $request->name,
            'link' => $request->link,
            'position' => $request->position,
            'status' => $request->status,
            'created_at' => now(),
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thêm menu thành công',
            'data' => $menu,
        ], 200);
    }

    // =========================
    // SHOW
    // =========================
    public function show($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'status' => false,
                'message' => 'Menu không tồn tại',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $menu,
        ], 200);
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'status' => false,
                'message' => 'Menu không tồn tại',
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'required|string|max:255',
            'position' => 'required|string|in:mainmenu,footermenu',
            'status' => 'required|integer|in:0,1',
        ]);

        $menu->update([
            'name' => $request->name,
            'link' => $request->link,
            'position' => $request->position,
            'status' => $request->status,
            'updated_at' => now(),
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật menu thành công',
        ], 200);
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json([
                'status' => false,
                'message' => 'Menu không tồn tại',
            ], 404);
        }

        $menu->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa menu thành công',
        ], 200);
    }
}
