<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequestAttachment;
use Illuminate\Support\Facades\Storage;

// Les photos des clients sont sur un disque privé : seul un administrateur peut les voir.
class AdminAttachmentController extends Controller
{
    public function show(int $attachment)
    {
        // Vérification d'abord, avant toute recherche en base : un non-admin reçoit 403 dans tous les cas.
        abort_unless(auth()->user()?->is_admin === true, 403);

        $file = ServiceRequestAttachment::findOrFail($attachment);
        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->response($file->path, $file->original_name);
    }
}
