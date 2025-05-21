<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use App\Models\Offre;
use App\Models\MatchingScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;

class MatchingScoreController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/matching-score",
     *     summary="Calcule le score de matching entre un candidat et une offre",
     *     tags={"MatchingScore"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"candidat_id","offre_id"},
     *             @OA\Property(property="candidat_id", type="integer", example=1),
     *             @OA\Property(property="offre_id", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Score de matching calculé et enregistré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Score de matching calculé et enregistré avec succès."),
     *             @OA\Property(property="matching_score", type="object",
     *                 @OA\Property(property="candidat_id", type="integer", example=1),
     *                 @OA\Property(property="offre_id", type="integer", example=5),
     *                 @OA\Property(property="matching_score", type="number", format="float", example=87.5),
     *                 @OA\Property(property="evaluation", type="string", example="Bon profil, quelques écarts mineurs."),
     *                 @OA\Property(property="points_forts", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="ecarts", type="array", @OA\Items(type="string")),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Candidat ou offre non trouvé ou fichier CV manquant",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Candidat ou offre non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors du calcul du score de matching",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erreur lors du calcul du score de matching: ...")
     *         )
     *     )
     * )
     */

    public function calculateMatchingScore(Request $request)
    {
        // Récupérer le candidat et l'offre depuis la base de données
        $candidat = Candidat::find($request->candidat_id);
        $offre = Offre::find($request->offre_id);

        if (!$candidat || !$offre) {
            return response()->json(['error' => 'Candidat ou offre non trouvé'], 404);
        }

        // Vérifier si le score de matching existe déjà
        $existingMatchingScore = MatchingScore::where('candidat_id', $request->candidat_id)
            ->where('offre_id', $request->offre_id)
            ->first();

        if ($existingMatchingScore) {
            return response()->json([
                'message' => 'Le score de matching existe déjà.',
                'matching_score' => $existingMatchingScore
            ], 200);
        }

        // Convertir le PDF du CV en texte
        $pdfParser = new Parser();
        $cvPath = storage_path('app/public/' . $candidat->cv);
        if (!file_exists($cvPath)) {
            return response()->json(['error' => 'Fichier CV introuvable'], 404);
        }
        $pdf = $pdfParser->parseFile($cvPath);
        $cv_text = $pdf->getText();

        // Envoyer les données à FastAPI pour obtenir le score de matching
        try {
            $response = Http::post('http://127.0.0.1:8003/match-cv-offre', [
                'cv' => $cv_text,
                'offre' => [
                    'poste' => $offre->poste,
                    'description' => $offre->description,
                    'niveauExperience' => $offre->niveauExperience,
                    'niveauEtude' => $offre->niveauEtude,
                    'responsabilite' => $offre->responsabilite,
                    'experience' => $offre->experience,
                    'pays' => $offre->pays,
                    'ville' => $offre->ville,
                ],
            ]);

            $data = $response->json();

            // Enregistrer toutes les données retournées
            $matchingScore = MatchingScore::create([
                'candidat_id' => $request->candidat_id,
                'offre_id' => $request->offre_id,
                'matching_score' => $data['score'],
                'evaluation' => $data['evaluation'] ?? null,
                'points_forts' => $data['points_forts'] ?? [],
                'ecarts' => $data['ecarts'] ?? [],
            ]);

            return response()->json([
                'message' => 'Score de matching calculé et enregistré avec succès.',
                'matching_score' => $matchingScore
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du calcul du score de matching: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/matching-score/{candidat_id}",
     *     summary="Afficher le score de matching pour un candidat",
     *     tags={"MatchingScore"},
     *     @OA\Parameter(
     *         name="candidat_id",
     *         in="path",
     *         description="ID du candidat",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Score de matching récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="matching_score", type="number", format="float", example=85.0),
     *             @OA\Property(property="evaluation", type="string", example="Très bon profil"),
     *             @OA\Property(property="points_forts", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="ecarts", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Score de matching non trouvé pour ce candidat",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Score de matching non trouvé pour ce candidat")
     *         )
     *     )
     * )
     */

    public function showMatchingScore($candidat_id)
    {
        // Récupérer le score de matching du candidat
        $matchingScore = MatchingScore::where('candidat_id', $candidat_id)->first();

        if (!$matchingScore) {
            return response()->json(['error' => 'Score de matching non trouvé pour ce candidat'], 404);
        }

        // Récupérer les données du score de matching
        $data = [
            'matching_score' => $matchingScore->matching_score,
            'evaluation' => $matchingScore->evaluation,
            'points_forts' => $matchingScore->points_forts,
            'ecarts' => $matchingScore->ecarts,
        ];

        // Retourner les données sous forme de réponse JSON
        return response()->json($data);
    }
}
