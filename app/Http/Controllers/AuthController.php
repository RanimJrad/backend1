<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecruiterAdded;
use Illuminate\Support\Facades\Log;




class AuthController extends Controller
{
    public function updateAdmin(Request $request, $id)
{
    // Validation des données d'entrée
    $validatedData = $request->validate([
        'departement' => 'nullable|string',
        'poste' => 'nullable|string',
    ]);

    // Récupérer l'utilisateur par son ID
    $user = User::find($id);

    if (!$user) {
        return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
    }

    // Vérifier si le champ département est fourni
    if ($request->has('departement')) {
        $user->departement = $validatedData['departement'];
    }

    // Vérifier si le champ poste est fourni
    if ($request->has('poste')) {
        $user->poste = $validatedData['poste'];
    }

    // Sauvegarder les modifications dans la base de données
    $user->save();

    // Retourner une réponse de succès
    return response()->json(['message' => 'Département et poste mis à jour avec succès.'], 200);
}
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('backendPFE')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ], 200);
    }


    public function register(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
        'departement' => 'required|string',
        'nom' => 'required|string',
        'prenom' => 'required|string',
        'numTel' => 'required|string',
        'poste' => 'required|string',
        'adresse' => 'required|string',
        'role' => 'required|string',
        'image' => 'required|file|mimes:jpeg,png,jpg|max:2048',
        'cv' => 'required|file|mimes:pdf|max:5120',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Stockage des fichiers
    $imagePath = $request->file('image')->store('images', 'public');
    $cvPath = $request->file('cv')->store('cv', 'public');

    // Création de l'utilisateur
    $user = User::create([
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'departement' => $request->departement,
        'nom' => $request->nom,
        'prenom' => $request->prenom,
        'numTel' => $request->numTel,
        'poste' => $request->poste,
        'adresse' => $request->adresse,
        'role' => $request->role,
        'image' => $imagePath,
        'cv' => $cvPath,
    ]);

    // Envoi de l'email
    Mail::to($user->email)->send(new RecruiterAdded($user->nom, $request->password));

    if (Mail::failures()) {
        // Loguer l'erreur ou afficher un message
        Log::error('Mail failed to send to ' . $user->email);
    } else {
        Log::info('Mail sent successfully to ' . $user->email);
    }

    // Génération du token d'authentification
    $token = $user->createToken('backendPFE')->plainTextToken;

    return response()->json([
        'message' => 'Registration successful and email sent!',
        'token' => $token,
        'user' => $user,
    ], 201);
}

    
}
