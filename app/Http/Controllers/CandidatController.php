<?php

namespace App\Http\Controllers;

use App\Models\Candidat;
use App\Models\Notification;
use App\Models\Offre;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CandidatController extends Controller
{

    /**
     * Ajouter un candidat.
     */
    public function storeCandidat(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email',
            'pays' => 'required|string|max:255',
            'ville' => 'required|string|max:255',
            'codePostal' => 'required|string|max:10',
            'tel' => 'required|string|max:20',
            'niveauEtude' => 'required|string|max:255',
            'niveauExperience' => 'nullable|string|max:255',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'offre_id' => 'required|exists:offres,id',
        ]);
    
        // Vérifier si le candidat a déjà postulé à cette offre avec le même email
        $existingApplication = Candidat::where('email', $request->email)
                                       ->where('offre_id', $request->offre_id)
                                       ->first();
    
        if ($existingApplication) {
            return response()->json([
                'error' => 'Vous avez déjà postulé à cette offre. Vous ne pouvez postuler qu\'une seule fois par offre.'
            ], 400);
        }
    
        // Sauvegarde du CV
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cvs', 'public'); // Sauvegarde dans storage/app/public/cvs
        }
    
        // Créer le candidat
        $candidat = Candidat::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'pays' => $request->pays,
            'ville' => $request->ville,
            'codePostal' => $request->codePostal,
            'tel' => $request->tel,
            'niveauEtude' => $request->niveauEtude,
            'niveauExperience' => $request->niveauExperience,
            'cv' => $cvPath ?? null,
            'offre_id' => $request->offre_id,
        ]);
    
        // Trouver l'offre à laquelle le candidat a postulé
        $offre = Offre::find($request->offre_id);
    
        if ($offre) {
            // Trouver les recruteurs de la même société
            $recruiters = User::where('nom_societe', $offre->societe)
                              ->where('role', 'recruteur')
                              ->where('active', true)
                              ->get();
    
            // Créer une notification pour chaque recruteur
            foreach ($recruiters as $recruiter) {
                Notification::create([
                    'type' => 'new_application',
                    'message' => "Un candidat a postulé à votre offre d'emploi '{$offre->poste}'",
                    'data' => [
                        'candidate_id' => $candidat->id,
                        'candidate_name' => $candidat->nom . ' ' . $candidat->prenom,
                        'candidate_email' => $candidat->email,
                        'offer_id' => $offre->id,
                        'position' => $offre->poste,
                        'department' => $offre->departement,
                        'company' => $offre->societe,
                        'application_id' => $candidat->id, // Pour navigation dans le frontend
                    ],
                    'user_id' => $recruiter->id,
                    'read' => false,
                ]);
            }
        }
    
        // Retourner la réponse avec le candidat créé
        return response()->json($candidat, 201);
    }
    
    
    
    public function showcandidatOffre()
    {
        $user = Auth::user(); // Récupérer l'utilisateur connecté
    
        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }
    
        // Filtrer les candidats non archivés dont l'offre appartient à la même société que l'utilisateur connecté
        $candidats = Candidat::where('archived', 0)
            ->whereHas('offre', function ($query) use ($user) {
                $query->where('societe', $user->nom_societe);
            })
            ->with(['offre:id,departement,domaine,datePublication,poste'])
            ->get();
    
        // Ajouter le chemin du CV pour chaque candidat
        foreach ($candidats as $candidat) {
            $candidat->cv = $candidat->cv ? asset('storage/' . $candidat->cv) : null;
        }
    
        return response()->json($candidats);
    }
    public function archiverCandidat($id)
    {
        // Récupérer le candidat par son ID
        $candidat = Candidat::find($id);
    
        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }
    
        // Mettre à jour le champ "archived"
        $candidat->archived = true;
        $candidat->save();
    
        return response()->json(['message' => 'Candidat archivé avec succès', 'candidat' => $candidat], 200);
    }

    public function getArchivedCandidatesByCompany(Request $request)
{
    $user = Auth::user(); // Récupérer l'utilisateur connecté

    if (!$user) {
        return response()->json(['message' => 'Utilisateur non authentifié'], 401);
    }

    // Filtrer les candidats non archivés dont l'offre appartient à la même société que l'utilisateur connecté
    $candidats = Candidat::where('archived', 1)
        ->whereHas('offre', function ($query) use ($user) {
            $query->where('societe', $user->nom_societe);
        })
        ->with(['offre:id,departement,domaine,datePublication,poste'])
        ->get();

    // Ajouter le chemin du CV pour chaque candidat
    foreach ($candidats as $candidat) {
        $candidat->cv = $candidat->cv ? asset('storage/' . $candidat->cv) : null;
    }

    return response()->json($candidats);
}

public function desarchiverCandidat($id)
    {
        // Récupérer le candidat par son ID
        $candidat = Candidat::find($id);
    
        if (!$candidat) {
            return response()->json(['message' => 'Candidat non trouvé'], 404);
        }
    
        // Mettre à jour le champ "archived"
        $candidat->archived = false;
        $candidat->save();
    
        return response()->json(['message' => 'Candidat archivé avec succès', 'candidat' => $candidat], 200);
    }

    public function rechercheCandidat($nom = null, $prenom = null)
{
    $query = Candidat::query();

    if (!empty($nom)) {
        $query->where('nom', 'like', '%' . $nom . '%');
    }
    if (!empty($prenom)) {
        $query->where('prenom', 'like', '%' . $prenom . '%');
    }

    return $query->get();
}
public function deleteCandidat($id)
{
    // Récupérer le candidat par son ID
    $candidat = Candidat::find($id);

    if (!$candidat) {
        return response()->json(['message' => 'Candidat non trouvé'], 404);
    }

    // Supprimer le fichier CV du stockage s'il existe
    if ($candidat->cv) {
        Storage::disk('public')->delete($candidat->cv);
    }

    // Supprimer le candidat de la base de données
    $candidat->delete();

    return response()->json(['message' => 'Candidat supprimé avec succès'], 200);
}

public function getCandidatsByOffre($offre_id)
{
    // Vérifier si l'offre existe
    $offre = Offre::find($offre_id);
    
    if (!$offre) {
        return response()->json(['message' => 'Offre non trouvée'], 404);
    }

    // Récupérer les candidats liés à cette offre
    $candidats = Candidat::where('offre_id', $offre_id)
        ->with('offre:id,poste,departement') // Charger les détails de l'offre
        ->get();

    // Ajouter l'URL du CV pour chaque candidat
    foreach ($candidats as $candidat) {
        $candidat->cv = $candidat->cv ? asset('storage/' . $candidat->cv) : null;
    }

    return response()->json($candidats, 200);
}
public function offresParCandidat($email)
{
    $candidat = DB::table('candidats')
        ->where('email', $email)
        ->join('offres', 'candidats.offre_id', '=', 'offres.id')
        ->select('candidats.nom', 'candidats.prenom', 'candidats.email', 'offres.*')
        ->get();

    if ($candidat->isEmpty()) {
        return response()->json(['message' => 'Aucune offre trouvée pour ce candidat'], 404);
    }

    return response()->json($candidat);
}


}