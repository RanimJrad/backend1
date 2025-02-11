<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Vérification des informations de l'utilisateur
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Génération du token API
        $token = $user->createToken('backendPFE')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ], 200);
    }



    public function register(Request $request)
{
    // Validation des données d'inscription
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|unique:users,email',  // Vérifie que l'email est unique
        'password' => 'required|min:8',  // Mot de passe requis avec une longueur minimale de 8 caractères
        'departement' => 'required|string',
        'nom' => 'required|string',
        'prenom' => 'required|string',
        'numTel' => 'required|string',
        'poste' => 'required|string',
        'adresse' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Création du nouvel utilisateur
    $user = User::create([
        'email' => $request->email,
        'password' => Hash::make($request->password),  // Hash du mot de passe
        'departement' => $request->departement,
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'numTel' => $request->numTel,
        'poste' => $request->poste,
        'adresse' => $request->adresse,
    ]);

    // Génération du token API
    $token = $user->createToken('backendPFE')->plainTextToken;

    return response()->json([
        'message' => 'Registration successful',
        'token' => $token,
        'user' => $user,
    ], 201); // Code 201 pour une ressource créée
}
    
}
