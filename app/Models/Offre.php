<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    use HasFactory;

    protected $fillable = [
        'departement',
        'poste',
        'description',
        'datePublication',
        'dateExpiration',
        'valider',
        'typePoste',
        'typeTravail',
        'heureTravail',
        'niveauExperience',
        'niveauEtude',
        'pays',
        'ville',
        'societe',
        'domaine',
        'responsabilite',
        'experience',
    ];
}