<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SeoMetaService;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __invoke(SeoMetaService $seo): View
    {
        $page = Page::findByRouteKey('contact.show');

        return view('contact.index', [
            'page' => $page,
            'meta' => $seo->forRoute('contact.show', 'İletişim | '.site_setting('site_name')),
        ]);
    }
}
