<?php

namespace App\Http\Controllers;

use App\Services\SeoMetaService;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __invoke(SeoMetaService $seo): View
    {
        return view('contact.index', [
            'meta' => [
                ...$seo->forDefault(),
                'title' => 'İletişim | '.config('app.name'),
                'description' => 'Telefon, e-posta, WhatsApp ve canlı destek üzerinden NewsTanıtım ekibine ulaşın.',
            ],
        ]);
    }
}
