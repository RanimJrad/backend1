<?php

// app/Http/Controllers/OffreScoreController.php

namespace App\Http\Controllers;

use App\Models\OffreScore;
use Illuminate\Http\Request;

class OffreScoreController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/offre-score",
     *     summary="Enregistrer un score pour une offre par un candidat",
     *     tags={"OffreScore"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"offre_id","candidat_id","score"},
     *             @OA\Property(property="offre_id", type="integer", example=10),
     *             @OA\Property(property="candidat_id", type="integer", example=3),
     *             @OA\Property(property="score", type="integer", minimum=1, maximum=5, example=4)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Score enregistré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Score enregistré avec succès."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="offre_id", type="integer", example=10),
     *                 @OA\Property(property="candidat_id", type="integer", example=3),
     *                 @OA\Property(property="score", type="integer", example=4),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Score déjà enregistré",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Score déjà enregistré.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */

    public function store(Request $request)
    {
        $request->validate([
            'offre_id' => 'required|exists:offres,id',
            'candidat_id' => 'required|exists:candidats,id',
            'score' => 'required|integer|min:1|max:5', // en supposant un score entre 1 et 5
        ]);

        // Vérifier s'il a déjà noté cette offre (optionnel)
        $existing = OffreScore::where('offre_id', $request->offre_id)
            ->where('candidat_id', $request->candidat_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Score déjà enregistré.'], 400);
        }

        $offreScore = OffreScore::create([
            'offre_id' => $request->offre_id,
            'candidat_id' => $request->candidat_id,
            'score' => $request->score,
        ]);

        return response()->json(['message' => 'Score enregistré avec succès.', 'data' => $offreScore]);
    }
    /**
     * @OA\Put(
     *     path="/api/offre-score",
     *     summary="Mettre à jour un score existant pour une offre par un candidat",
     *     tags={"OffreScore"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"offre_id","candidat_id","score"},
     *             @OA\Property(property="offre_id", type="integer", example=10),
     *             @OA\Property(property="candidat_id", type="integer", example=3),
     *             @OA\Property(property="score", type="integer", minimum=1, maximum=5, example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Score mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Score mis à jour avec succès."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="offre_id", type="integer", example=10),
     *                 @OA\Property(property="candidat_id", type="integer", example=3),
     *                 @OA\Property(property="score", type="integer", example=5),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Score non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Score non trouvé.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation"
     *     )
     * )
     */

    public function update(Request $request)
    {
        $request->validate([
            'offre_id' => 'required|exists:offres,id',
            'candidat_id' => 'required|exists:candidats,id',
            'score' => 'required|integer|min:1|max:5',
        ]);

        // Trouver le score existant
        $offreScore = OffreScore::where('offre_id', $request->offre_id)
            ->where('candidat_id', $request->candidat_id)
            ->first();

        if (!$offreScore) {
            return response()->json(['message' => 'Score non trouvé.'], 404);
        }

        // Mettre à jour le score
        $offreScore->score = $request->score;
        $offreScore->save();

        return response()->json(['message' => 'Score mis à jour avec succès.', 'data' => $offreScore]);
    }
}
