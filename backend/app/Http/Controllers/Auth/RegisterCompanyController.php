<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterCompanyController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        return DB::transaction(function () use ($validated) {

            // 1️⃣ Crear empresa
            $company = Company::create([
                'name' => $validated['company_name'],
                'industry' => $validated['industry'],
            ]);

            // 2️⃣ Crear usuario owner
            $user = User::create([
                'company_id' => $company->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            return response()->json([
                'message' => 'Empresa creada correctamente',
                'company' => $company,
                'user' => $user
            ], 201);
        });
    }
}