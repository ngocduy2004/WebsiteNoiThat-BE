<?php

namespace App\Http\Controllers;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', '%' . $search . '%');
        }

        $total = $query->count();

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



        $contacts = $query
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();


        return response()->json([
            'status' => true,
            'data' => $contacts,
            'total' => $total,
            'message' => 'Lấy danh sách liên hệ thành công',
        ]);
    }

    // =========================
    // STORE
    // =========================
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'content' => 'required',
            'status' => 'required|integer',
        ]);

        $data = Contact::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'content' => $request->input('content'),
            'reply_id' => null,
            'status' => $request->status,
            'created_at' => now(),
            'created_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Gửi liên hệ thành công',
            'data' => $data,
        ], 200);
    }

    // =========================
    // SHOW DETAIL
    // =========================
    public function show($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Liên hệ không tồn tại',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $contact,
        ], 200);
    }

    // =========================
    // UPDATE
    // =========================
    public function update(Request $request, $id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Liên hệ không tồn tại',
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'content' => 'required',
            'reply_id' => 'nullable|integer',
            'status' => 'required|integer',
        ]);

        $contact->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'content' => $request->input('content'),
            'reply_id' => $request->reply_id,
            'status' => $request->status,
            'updated_at' => now(),
            'updated_by' => Auth::id() ?? 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật liên hệ thành công',
        ], 200);
    }

    // =========================
    // DELETE
    // =========================
    public function destroy($id)
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json([
                'status' => false,
                'message' => 'Liên hệ không tồn tại',
            ], 404);
        }

        $contact->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa liên hệ thành công',
        ], 200);
    }
}
