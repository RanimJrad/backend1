<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class Candidat extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'prenom', 'email', 'pays', 'ville', 'codePostal', 'niveauExperience', 'tel', 'niveauEtude', 'cv', 'offre_id','archived'
    ];

    /**
     * Relation avec l'offre
     */
    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }
   
}