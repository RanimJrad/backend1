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

    public function archiveUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
        }

        $user->archived = true;
        $user->save();

        return response()->json(['message' => 'Utilisateur archivé avec succès.'], 200);
    }
    public function getArchivedUsers()
    {
        $archivedUsers = User::where('archived', true)->get();

        return response()->json($archivedUsers, 200);
    }

}
