<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Support\Facades\File; // Thêm thư viện File

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

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


        if ($request->has('search') && $request->search != "") {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        $query->orderBy('created_at', 'desc');

        $categories = $query->get();

        $total = Category::count();

        return response()->json([
            'status' => true,
            'data' => $categories,
            'total' => $total,
            'message' => 'Lấy danh sách Danh mục thành công',
            'error' => null,
        ], 200);
    }
    public function tree()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $categories
        ], 200);
    }
    public function create()
    {
        return view('admin.category.add');
    }

    public function store(Request $request)
    {
        // Validate
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug', // Thêm unique để tránh trùng slug
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,webp,gif'
        ]);

        $filename = null;
        // 1. Xử lý lưu ảnh (Giống hệt hàm update)
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/category'), $filename);
        }

        // 2. Xử lý parent_id: Nếu là 0 thì chuyển thành null
        $parentId = $request->parent_id;
        if ($parentId == 0) {
            $parentId = null;
        }

        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'image' => $filename, // Chỉ lưu tên file
            'parent_id' => $parentId, // Lưu null nếu là danh mục cha
            'sort_order' => $request->sort_order ?? 0,
            'description' => $request->description,
            'created_by' => 1,
            'status' => $request->status ?? 1,
        ]);

        return response()->json([
            "status" => true,
            "message" => "Thêm danh mục thành công!"
        ], 200);
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Danh mục không tồn tại',
            ], 404);
        }
        return response()->json([
            'status' => true,
            'data' => [
                ...$category->toArray(),
                'image_url' => $category->image
                    ? asset('uploads/category/' . $category->image)
                    : null
            ],
            'message' => 'Lấy danh mục thành công',
        ], 200);
    }
    // SỬA LẠI HÀM UPDATE
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['status' => false, 'message' => 'Danh mục không tồn tại'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255', // Nếu cần unique thì: unique:categories,slug,'.$id
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,webp,gif'
        ]);

        // Xử lý ảnh
        if ($request->hasFile('image')) {
            // 1. Xóa ảnh cũ nếu có (để dọn dẹp server)
            $oldImagePath = public_path('uploads/category/' . $category->image);
            if ($category->image && File::exists($oldImagePath)) {
                File::delete($oldImagePath);
            }

            // 2. Lưu ảnh mới
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/category'), $filename);

            // Cập nhật tên ảnh mới vào mảng dữ liệu
            $validated['image'] = $filename;
        }

        // Xử lý slug (đảm bảo slug được cập nhật nếu tên thay đổi)
        $validated['slug'] = Str::slug($request->slug);

        $category->update($validated);

        return response()->json([
            'status' => true,
            'data' => $category,
            'message' => 'Cập nhật danh mục thành công'
        ]);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Danh mục không tồn tại'
            ], 404);
        }

        // Xóa ảnh
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa danh mục thành công'
        ], 200);
    }

    // ... các hàm trên giữ nguyên ...

    // 🔥 HÀM QUAN TRỌNG CẦN SỬA 🔥
    public function products($slug)
    {
        $now = \Carbon\Carbon::now('Asia/Ho_Chi_Minh');

        // 1. Tìm danh mục
        $category = Category::where('slug', $slug)
            ->with('children') // Load danh mục con
            ->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Danh mục không tồn tại'
            ], 404);
        }

        // 2. Lấy ID của danh mục hiện tại + các danh mục con
        // Ví dụ: Vào "Áo nam" thì phải lấy cả sản phẩm của "Áo thun", "Áo sơ mi"...
        $categoryIds = collect([$category->id])
            ->merge($category->children->pluck('id'));

        // 3. Query Sản phẩm kèm Logic Sale (Giống ProductController)
        $products = Product::whereIn('products.category_id', $categoryIds)
            ->where('products.status', 1)

            // --- JOIN SALE START ---
            ->leftJoin('product_sale_items', 'products.id', '=', 'product_sale_items.product_id')
            ->leftJoin('product_sale', function ($join) use ($now) {
                $join->on('product_sale_items.product_sale_id', '=', 'product_sale.id')
                    ->where('product_sale.status', 1) // Sale đang bật
                    ->where('product_sale.date_begin', '<=', $now) // Đã bắt đầu
                    ->where('product_sale.date_end', '>=', $now);  // Chưa kết thúc
            })
            // --- JOIN SALE END ---

            ->select(
                'products.*', // Lấy tất cả cột bảng products
                'product_sale_items.price_sale as sale_price', // Lấy giá sale
                'product_sale.date_end as sale_end_date'
            )
            ->orderBy('products.created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'category' => $category,
            'products' => $products
        ]);
    }
}
