<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{Hash, Validator};

class CreateTechnician extends Command
{
    protected $signature = 'technician:create';
    protected $description = 'Crée (ou met à jour le mot de passe d’) un compte technicien';

    public function handle(): int
    {
        $name = $this->ask('Nom');
        $email = $this->ask('E-mail (sert d’identifiant)');
        $password = $this->secret('Mot de passe (12 caractères minimum)');

        $v = Validator::make(compact('name', 'email', 'password'), ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email'], 'password' => ['required', 'string', 'min:12']]);
        if ($v->fails()) {
            foreach ($v->errors()->all() as $e) $this->error($e);
            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->is_technician = true; // affecté explicitement, jamais via formulaire
        $user->save();

        $this->info("Technicien prêt : {$email} (connexion : /technicien)");
        return self::SUCCESS;
    }
}
