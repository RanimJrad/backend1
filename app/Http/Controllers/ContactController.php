<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'sujet' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
    
        // Création du contact dans la base de données
        $contact = Contact::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'sujet' => $request->sujet,
            'message' => $request->message,
        ]);
    
        // Créer une notification pour les admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'type' => 'new_contact',
                'message' => "Nouveau message de contact: {$request->sujet}",
                'data' => [
                    'contact_id' => $contact->id,
                    'name' => $contact->nom,
                    'email' => $contact->email,
                    'subject' => $contact->sujet,
                    'message_preview' => substr($contact->message, 0, 100) . (strlen($contact->message) > 100 ? '...' : ''),
                ],
                'user_id' => $admin->id,
                'read' => false,
            ]);
        }
    
        // Retourner une réponse JSON (ou redirection si utilisé en web)
        return response()->json([
            'message' => 'Message envoyé avec succès !',
            'contact' => $contact
        ], 201);
    }
    public function index()
    {
        $contacts = Contact::all(); // Récupérer tous les contacts
        return response()->json($contacts); // Retourner les données en JSON
    }

    public function deleteContact($id)
    {
        $contact = Contact::find($id);
    
        if (!$contact) {
            return response()->json(['message' => 'Contact non trouvé'], 404);
        }
    
        $contact->delete();
    
        return response()->json(['message' => 'Contact supprimé avec succès']);
    }

    public function markAsReplied($id)
    {
        $contact = Contact::find($id);
        
        if (!$contact) {
            return response()->json(['message' => 'Contact non trouvé'], 404);
        }
        
        $contact->repondu = true;
        $contact->save();
        
        return response()->json(['message' => 'Contact marqué comme répondu', 'contact' => $contact]);
    }
}
