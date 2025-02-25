<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/users",
 *     summary="Récupérer tous les recruteurs non archivés",
 *     description="Obtenir une liste de tous les recruteurs actifs",
 *     tags={"Utilisateurs"},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des recruteurs",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="email", type="string", example="user@example.com"),
 *                 @OA\Property(property="image", type="string", example="http://example.com/storage/images/user.jpg"),
 *                 @OA\Property(property="cv", type="string", example="http://example.com/storage/cv/user.pdf"),
 *                 @OA\Property(property="role", type="string", example="recruteur"),
 *                 @OA\Property(property="archived", type="boolean", example=false)
 *             )
 *         )
 *     )
 * )
 */
    public function index()
{
    $recruteurs = User::where('role', 'recruteur')
                      ->where('archived', 0)
                      ->get();

    foreach ($recruteurs as $recruteur) {
        $recruteur->image = $recruteur->image ? asset('storage/' . $recruteur->image) : null;
        $recruteur->cv = $recruteur->cv ? asset('storage/' . $recruteur->cv) : null;
    }

    return response()->json($recruteurs);
}
  
    /**
 * @OA\Put(
 *     path="/api/users/unarchive/{id}",
 *     summary="Désarchiver un utilisateur",
 *     description="Désarchiver un utilisateur par son ID",
 *     tags={"Utilisateurs"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Utilisateur désarchivé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Utilisateur désarchivé avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Utilisateur non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Utilisateur non trouvé.")
 *         )
 *     )
 * )
 */
    public function unarchiveUser($id)
    {
        $user = User::find($id);
    
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé.'], 404);
        }
    
        $user->archived = false;
        $user->save();
    
        return response()->json(['message' => 'Utilisateur désarchivé avec succès.'], 200);
    }
    /**
 * @OA\Put(
 *     path="/api/users/archive/{id}",
 *     summary="Archiver un utilisateur",
 *     description="Archiver un utilisateur par son ID",
 *     tags={"Utilisateurs"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Utilisateur archivé avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Utilisateur archivé avec succès")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Utilisateur non trouvé",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Utilisateur non trouvé.")
 *         )
 *     )
 * )
 */
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
/**
 * @OA\Get(
 *     path="/api/users/archived",
 *     summary="Récupérer tous les utilisateurs archivés",
 *     description="Obtenir une liste de tous les utilisateurs archivés",
 *     tags={"Utilisateurs"},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des utilisateurs archivés",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="email", type="string", example="user@example.com"),
 *                 @OA\Property(property="cv", type="string", example="http://example.com/storage/cv/user.pdf"),
 *                 @OA\Property(property="role", type="string", example="recruteur"),
 *                 @OA\Property(property="archived", type="boolean", example=true)
 *             )
 *         )
 *     )
 * )
 */

    public function getArchivedUsers()
    {
        $archivedUsers = User::where('archived', true)->get();
        foreach ($archivedUsers as $recruteur) {
            $recruteur->cv = $recruteur->cv ? asset('storage/' . $recruteur->cv) : null;
        }
        return response()->json($archivedUsers, 200);
    }

    public function getCurrentUserInfo()
    {
        $user = Auth::user();
        
        return response()->json([
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'image' => $user->image ? asset('storage/' . $user->image) : null,
        ]);

    }

}