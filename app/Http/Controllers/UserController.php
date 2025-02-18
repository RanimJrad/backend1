<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $recruteurs = User::where('role', 'recruteur')->get();
        

        foreach ($recruteurs as $recruteur) {
            $recruteur->image = $recruteur->image ? asset('storage/' . $recruteur->image) : null;
            $recruteur->cv = $recruteur->cv ? asset('storage/' . $recruteur->cv) : null;


        }
    
        return response()->json($recruteurs);
    }
    
    public function destroy($id)
    {
        $user = User::find($id);

        if ($user) {
            $user->delete();

            return response()->json(['message' => 'Utilisateur supprimé avec succès'], 200);
        }

        return response()->json(['message' => 'Utilisateur introuvable'], 404);
    }
}
