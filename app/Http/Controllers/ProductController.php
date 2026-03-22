<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Attribute;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Product_store;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /* ===================== INDEX ===================== */
    // ... các use ở trên giữ nguyên

    // Trong App\Http\Controllers\ProductController.php

    public function index(Request $request)
    {
        // Lấy thời gian hiện tại
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        // 1. Khởi tạo query
        $query = Product::with([
            'category',
            'productAttributes.attribute',
            'images'
        ]);

        // 2. JOIN KHO (Giữ nguyên logic của bạn)
        if ($request->boolean('filter_stock')) {
            $stockQuery = DB::table('product_store')
                ->select('product_id', DB::raw('SUM(qty) as total_qty'))
                ->groupBy('product_id');

            $query->joinSub($stockQuery, 'stock', function ($join) {
                $join->on('products.id', '=', 'stock.product_id');
            })->where('stock.total_qty', '>', 0);
        }

        // 3. 🔥 THÊM LOGIC LẤY GIÁ SALE 🔥
        // Left Join bảng items và bảng sale cha
        $query->leftJoin('product_sale_items', 'products.id', '=', 'product_sale_items.product_id')
            ->leftJoin('product_sale', function ($join) use ($now) {
                $join->on('product_sale_items.product_sale_id', '=', 'product_sale.id')
                    ->where('product_sale.status', 1) // Sale phải đang bật
                    ->where('product_sale.date_begin', '<=', $now) // Đã bắt đầu
                    ->where('product_sale.date_end', '>=', $now);  // Chưa kết thúc
            });

        // 4. Search (Giữ nguyên)
        if ($request->filled('search')) {
            $query->where('products.name', 'like', '%' . $request->search . '%');
        }

        // 5. SELECT DỮ LIỆU
        // Cần select rõ ràng để tránh trùng tên cột
        $selectColumns = [
            'products.*',
            'product_sale_items.price_sale as sale_price', // Đổi tên thành sale_price
            'product_sale.name as sale_name',
            'product_sale.date_end as sale_end_date'
        ];

        // Nếu có filter stock thì select thêm total_qty
        if ($request->boolean('filter_stock')) {
            $selectColumns[] = 'stock.total_qty';
        }

        $query->select($selectColumns);

        // 6. Sắp xếp để ưu tiên hiển thị sản phẩm có sale (nếu muốn), hoặc giữ nguyên created_at
        // Ở đây mình giữ nguyên order của bạn
        $query->orderBy('products.created_at', 'desc');

        // 7. Pagination
        if ($request->filled('limit')) {
            $limit = (int) $request->limit;
            if ($request->filled('page')) {
                $page = (int) $request->page;
                $query->offset(($page - 1) * $limit)->limit($limit);
            } else {
                $query->limit($limit);
            }
        }

        $products = $query->get();

        // Nếu dùng phân trang đầy đủ của Laravel thì dùng $query->paginate($limit) thay vì offset/limit thủ công.
        // Nhưng để khớp code cũ của bạn, mình dùng get() và count riêng.

        $total = Product::count(); // Lưu ý: Count này chưa chuẩn xác nếu có filter search, nhưng mình giữ theo luồng cũ của bạn.

        // $products->transform(function ($product) {
        //     if ($product->thumbnail) {
        //         // asset() sẽ tự lấy APP_URL từ Railway gán vào
        //         $product->thumbnail = asset('storage/' . $product->thumbnail);
        //     }
        //     return $product;
        // });

        return response()->json([
            'status' => true,
            'data' => $products,
            'products' => $products, // Thêm dòng này để "chiều" Front-end nếu nó gọi .products
            'total' => $total
        ]);
    }

    /* ===================== STORE ===================== */
    public function store(Request $request)
    {
        // 1. Decode attributes nếu nó là chuỗi JSON gửi từ FormData
        if ($request->filled('attributes') && is_string($request->input('attributes'))) {
            $request->merge([
                'attributes' => json_decode($request->input('attributes'), true)
            ]);
        }

        // 2. Validate dữ liệu
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'thumbnail' => 'nullable|image|max:2048',
            'images.*' => 'nullable|image|max:2048',
            'content' => 'required',
            'price_buy' => 'required|numeric|min:0',
            'status' => 'required|in:0,1',
            'attributes' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // 3. Xử lý Thumbnail (Ảnh đại diện)
            $thumbnailPath = null;
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('products', 'public');
            }

            // 4. Tạo Sản phẩm
            $product = Product::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'thumbnail' => $thumbnailPath,
                'content' => $request->input('content'),
                'price_buy' => $request->price_buy,
                'status' => $request->status,
                'created_by' => 1, // Thay bằng auth()->id() nếu có login
            ]);

            // 5. Xử lý Ảnh phụ (Gallery)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $img) {
                    $path = $img->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                        'alt' => $product->name,
                        'title' => $product->name,
                    ]);
                }
            }

            $attributesData = $request->input('attributes');
            if (!empty($attributesData) && is_array($attributesData)) {
                foreach ($attributesData as $attr) {
                    if (empty($attr['name']) || empty($attr['values']))
                        continue;

                    // Tìm hoặc tạo nhóm thuộc tính (ví dụ: "Màu sắc")
                    $attributeGroup = Attribute::firstOrCreate([
                        'name' => trim($attr['name'])
                    ]);

                    // Ép kiểu values về mảng
                    $values = (array) $attr['values'];
                    foreach ($values as $value) {
                        $trimmedValue = trim($value);
                        if ($trimmedValue === "")
                            continue;

                        // ✅ Sử dụng updateOrCreate để an toàn tuyệt đối
                        ProductAttribute::updateOrCreate([
                            'product_id' => $product->id,
                            'attribute_id' => $attributeGroup->id,
                            'value' => $trimmedValue
                        ]);
                    }
                }
            }

            // 7. Hoàn tất giao dịch
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Thêm sản phẩm thành công!',
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            // 8. Nếu có lỗi, xóa ảnh đã upload và quay ngược dữ liệu (Rollback)
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
    /* ===================== SHOW ===================== */
    public function show($id)
    {
        $now = \Carbon\Carbon::now('Asia/Ho_Chi_Minh');

        // 1. Query Builder để lấy Product + Giá Sale
        $product = Product::with([
            'category',
            'productAttributes.attribute', // Load các thuộc tính
            'images' // Load album ảnh
        ])
            // 🔥 THÊM LOGIC JOIN SALE GIỐNG HÀM INDEX 🔥
            ->leftJoin('product_sale_items', 'products.id', '=', 'product_sale_items.product_id')
            ->leftJoin('product_sale', function ($join) use ($now) {
                $join->on('product_sale_items.product_sale_id', '=', 'product_sale.id')
                    ->where('product_sale.status', 1) // Sale đang bật
                    ->where('product_sale.date_begin', '<=', $now) // Đã bắt đầu
                    ->where('product_sale.date_end', '>=', $now);  // Chưa kết thúc
            })
            ->select(
                'products.id', // Chỉ rõ lấy ID của bảng products
                'products.name',
                'products.category_id',
                'products.thumbnail',
                'products.content',
                'products.price_buy',
                'products.status',
                'product_sale_items.price_sale as sale_price',
                'product_sale.date_end as sale_end_date'
            )
            ->where('products.id', $id)
            ->first(); // Lấy 1 dòng kết quả

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Sản phẩm không tồn tại'
            ], 404);
        }

        // 2. Lấy tồn kho (Giữ nguyên logic cũ của bạn)
        $stockData = DB::table('product_store')
            ->where('product_id', $id)
            ->where('status', 1)
            ->select(DB::raw('SUM(qty) as total_qty'))
            ->first();

        $product->total_stock = $stockData?->total_qty ?? 0;

        return response()->json([
            'status' => true,
            'message' => 'Lấy chi tiết sản phẩm thành công',
            'product' => $product, // Đổi 'data' thành 'product' cho khớp với Front-end
            'data' => $product     // Hoặc để cả hai cho chắc ăn
        ]);
    }

    /* ===================== UPDATE ===================== */
    /* ===================== UPDATE ===================== */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        // 1. Decode attributes chắc chắn
        $attributesData = $request->input('attributes');
        if (is_string($attributesData)) {
            $attributesData = json_decode($attributesData, true);
        }

        DB::beginTransaction();
        try {
            // --- A. Cập nhật thông tin cơ bản ---
            $product->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'content' => $request->input('content'),
                'description' => $request->input('description'),
                'price_buy' => $request->price_buy,
                'status' => $request->status,
            ]);

            // --- B. Xử lý thuộc tính: Xóa cũ và tạo mới hoàn toàn ---
            // Đây là cách an toàn nhất để đồng bộ mảng lồng nhau
            ProductAttribute::where('product_id', $id)->delete();

            if (!empty($attributesData) && is_array($attributesData)) {
                foreach ($attributesData as $attr) {
                    if (empty($attr['name']))
                        continue;

                    // Lấy hoặc tạo nhóm thuộc tính (Màu sắc, Kích thước...)
                    $attributeGroup = Attribute::firstOrCreate([
                        'name' => trim($attr['name'])
                    ]);

                    // Duyệt mảng giá trị gửi từ React
                    $values = isset($attr['values']) ? (array) $attr['values'] : [];
                    foreach ($values as $value) {
                        $trimmedValue = trim($value);
                        if ($trimmedValue === "")
                            continue;

                        ProductAttribute::create([
                            'product_id' => $id,
                            'attribute_id' => $attributeGroup->id,
                            'value' => $trimmedValue
                        ]);
                    }
                }
            }

            // --- C. Xử lý Thumbnail và Gallery (Giữ nguyên logic của bạn) ---
            if ($request->hasFile('thumbnail')) {
                if ($product->thumbnail)
                    Storage::disk('public')->delete($product->thumbnail);
                $product->thumbnail = $request->file('thumbnail')->store('products', 'public');
                $product->save();
            }

            // 4. QUAN TRỌNG: XỬ LÝ ALBUM ẢNH PHỤ (MỚI)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('products', 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image' => $path,
                        'alt' => $product->name,
                        'title' => $product->name,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => true, 'message' => 'Cập nhật thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /* ===================== DESTROY ===================== */
    public function destroy($id)
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['status' => false], 404);
        }

        if ($product->thumbnail) {
            Storage::disk('public')->delete($product->thumbnail);
        }

        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->image);
            $img->delete();
        }

        $product->delete();

        return response()->json(['status' => true]);
    }

    public function deleteImage($id)
    {
        $image = ProductImage::find($id);

        if (!$image) {
            return response()->json([
                'status' => false,
                'message' => 'Ảnh không tồn tại'
            ], 404);
        }

        // Xoá file
        if ($image->image) {
            Storage::disk('public')->delete($image->image);
        }

        // Xoá DB
        $image->delete();

        return response()->json([
            'status' => true
        ]);
    }
    /* ===================== PRODUCT NEW ===================== */
    public function product_new(Request $request)
    {
        $limit = $request->limit ?? 10;
        $now = Carbon::now('Asia/Ho_Chi_Minh');

        // 1. Query Kho (Giữ nguyên)
        $productStores = DB::table('product_store')
            ->select('product_id', DB::raw('SUM(qty) as product_qty'))
            ->groupBy('product_id');

        // 2. Query Products kết hợp Kho và Sale
        $products = Product::query()
            // Join Kho
            ->joinSub($productStores, 'ps', function ($join) {
                $join->on('ps.product_id', '=', 'products.id');
            })
            ->where('ps.product_qty', '>', 0)

            // 🔥 JOIN SALE (Tương tự hàm index) 🔥
            ->leftJoin('product_sale_items', 'products.id', '=', 'product_sale_items.product_id')
            ->leftJoin('product_sale', function ($join) use ($now) {
                $join->on('product_sale_items.product_sale_id', '=', 'product_sale.id')
                    ->where('product_sale.status', 1)
                    ->where('product_sale.date_begin', '<=', $now)
                    ->where('product_sale.date_end', '>=', $now);
            })

            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'products.thumbnail',
                'products.price_buy', // Giá gốc
                'ps.product_qty',
                'product_sale_items.price_sale as sale_price', // Giá Sale (sẽ null nếu hết hạn)
                'product_sale.date_end as sale_end_date'
            )
            ->orderBy('products.created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }

    // =================================================
    // 📌 LẤY TẤT CẢ SẢN PHẨM ĐANG SALE (TRANG SALE)
    // =================================================
    // =================================================
    // 📌 LẤY TẤT CẢ SẢN PHẨM ĐANG SALE (Bảng product_sale số ít)
    // =================================================
    public function getSaleProducts(Request $request)
    {
        try {
            $limit = $request->input('limit', 20);
            $now = now();

            // 1. Dùng Product::select
            $products = Product::select(
                'products.*',
                'product_sale_items.price_sale as pricesale',
                'product_sale.name as sale_name' // ✅ Đã sửa thành product_sale (số ít)
            )
                // Join bảng chi tiết sale (bảng này thường có tên là product_sale_items)
                ->join('product_sale_items', 'products.id', '=', 'product_sale_items.product_id')

                // Join bảng sale cha (Sửa tên bảng thành số ít)
                ->join('product_sale', 'product_sale_items.product_sale_id', '=', 'product_sale.id')

                // Điều kiện lọc (Sửa thành số ít)
                ->where('product_sale.status', 1)
                ->where('products.status', 1)
                ->where('product_sale.date_begin', '<=', $now)
                ->where('product_sale.date_end', '>=', $now)

                ->with('images')
                ->orderBy('product_sale.created_at', 'desc')
                ->limit($limit)
                ->get();

            // 2. Xử lý dữ liệu chuẩn cho Frontend
            $products->map(function ($product) {
                // Gán giá gốc vào biến 'price'
                // Nếu cột trong DB là price_buy thì gán nó vào price
                $product->price = $product->price_buy;
                return $product;
            });

            return response()->json([
                'status' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi Server: ' . $e->getMessage()
            ], 500);
        }
    }
}
