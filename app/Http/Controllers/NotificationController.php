<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        return response()->json([
            'notifications' => $user->notifications()->orderBy('created_at', 'desc')->get(),
            'unread_count' => $user->notifications()->where('read', false)->count()
        ]);
    }
    
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update(['read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }
    
    public function markAllAsRead()
    {
        auth()->user()->notifications()->where('read', false)->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

  
    
    
    


}