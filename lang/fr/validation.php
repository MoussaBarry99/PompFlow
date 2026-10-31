<?php

return [
    'required' => 'Le champ :attribute est obligatoire.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'string' => 'Le champ :attribute doit être du texte.',
    'in' => 'La valeur choisie pour :attribute est invalide.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'unique' => 'La valeur de :attribute est déjà utilisée.',
    'min' => [
        'numeric' => 'Le champ :attribute doit être au moins égal à :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'max' => [
        'numeric' => 'Le champ :attribute ne peut pas dépasser :max.',
        'string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
    ],
    'attributes' => [
        'type' => 'type',
        'montant' => 'montant',
        'description' => 'détail',
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
    ],
];
