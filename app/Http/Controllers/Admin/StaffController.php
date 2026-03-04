<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    // الأدمن بيضيف موظف خدمة عملاء للمنصة
    public function addStaff(Request $request)
    {
        // التأكد أن اللي بينفذ الأمر هو الأدمن الأساسي فقط
        if (auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مسموح لغير الأدمن بإضافة موظفين'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'phone' => 'nullable|string',
        ]);

        $employee = User::create([
            'name' => $request->name,
            'email' => $request->email,
             'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'employee', // هنا بنحدد إنه موظف منصة
            'is_active' => true,
        ]);

        return response()->json(['message' => 'تم إضافة موظف للمنصة بنجاح', 'staff' => $employee]);
    }
}