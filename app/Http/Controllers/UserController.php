<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }
    public function destroy($id)
    {
        // Récupérer l'utilisateur par son ID
        $user = User::find($id);

        if ($user) {
            // Supprimer l'utilisateur
            $user->delete();

            return response()->json(['message' => 'Utilisateur supprimé avec succès'], 200);
        }

        return response()->json(['message' => 'Utilisateur introuvable'], 404);
    }
}
