<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function getContactableUsers()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $user = Auth::user();
        
        try {
            if ($user->role === 'admin') {
                // L'admin peut voir tous les recruteurs
                // Récupérer les recruteurs avec leur dernier message
                $users = User::where('role', 'recruteur')
                    ->get()
                    ->map(function ($user) {
                        // Trouver le dernier message échangé avec cet utilisateur
                        $lastMessage = Message::where(function ($query) use ($user) {
                            $query->where('from_user_id', Auth::id())
                                ->where('to_user_id', $user->id);
                        })->orWhere(function ($query) use ($user) {
                            $query->where('from_user_id', $user->id)
                                ->where('to_user_id', Auth::id());
                        })
                        ->orderBy('created_at', 'desc')
                        ->first();
                        
                        // Ajouter la date du dernier message à l'utilisateur
                        $user->last_message_at = $lastMessage ? $lastMessage->created_at : null;
                        return $user;
                    })
                    ->sortByDesc('last_message_at') // Trier par date du dernier message
                    ->values(); // Réindexer le tableau
            } else {
                // Les recruteurs ne peuvent voir que les admins
                $users = User::where('role', 'admin')->get();
            }
            
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'content' => 'required|string'
        ]);

        try {
            $message = Message::create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $request->to_user_id,
                'content' => $request->content
            ]);

            // Charger les relations pour la réponse
            $message->load('sender');
            
            // Déclencher l'événement pour la diffusion en temps réel
            event(new NewMessage($message));

            return response()->json($message);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMessages($userId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        try {
            $messages = Message::where(function ($query) use ($userId) {
                $query->where('from_user_id', Auth::id())
                    ->where('to_user_id', $userId);
            })->orWhere(function ($query) use ($userId) {
                $query->where('from_user_id', $userId)
                    ->where('to_user_id', Auth::id());
            })
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markAsRead($messageId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        try {
            $message = Message::findOrFail($messageId);
            
            if ($message->to_user_id === Auth::id()) {
                $message->update(['read_at' => now()]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function markAllAsRead($userId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        try {
            Message::where('from_user_id', $userId)
                ->where('to_user_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUnreadCounts()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        try {
            $counts = Message::where('to_user_id', Auth::id())
                ->whereNull('read_at')
                ->groupBy('from_user_id')
                ->selectRaw('from_user_id, count(*) as count')
                ->pluck('count', 'from_user_id');

            return response()->json(['counts' => $counts]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUnreadTotal()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        try {
            $count = Message::where('to_user_id', Auth::id())
                ->whereNull('read_at')
                ->count();

            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
