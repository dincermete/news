<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\PromotionalListingType;
use App\Enums\SiteStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SiteResource;
use App\Models\Site;
use App\Support\CatalogQuery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SiteController extends Controller
{
    /**
     * Aktif site kataloğunu filtreleyerek listeler.
     *
     * @queryParam category_id int Kategori ID.
     * @queryParam min_price number Minimum fiyat.
     * @queryParam max_price number Maksimum fiyat.
     * @queryParam min_da number Minimum DA.
     * @queryParam max_da number Maksimum DA.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'category_id' => ['sometimes', 'integer', 'exists:site_categories,id'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
            'min_da' => ['sometimes', 'numeric', 'min:0'],
            'max_da' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $query = CatalogQuery::activeSitesWithListing(PromotionalListingType::SiteArticle)
            ->when(
                isset($validated['category_id']),
                fn ($q) => $q->where('sites.site_category_id', $validated['category_id']),
            )
            ->when(
                isset($validated['min_price']),
                fn ($q) => $q->where('promotional_listings.price', '>=', $validated['min_price']),
            )
            ->when(
                isset($validated['max_price']),
                fn ($q) => $q->where('promotional_listings.price', '<=', $validated['max_price']),
            )
            ->when(
                isset($validated['min_da']),
                fn ($q) => $q->where('sites.da_value', '>=', $validated['min_da']),
            )
            ->when(
                isset($validated['max_da']),
                fn ($q) => $q->where('sites.da_value', '<=', $validated['max_da']),
            )
            ->orderBy('sites.domain');

        $paginator = $query->paginate(50);
        $paginator->getCollection()->each(fn (Site $site) => $site->normalizeJoinedPricingAttributes());

        return SiteResource::collection($paginator);
    }

    /**
     * Tekil site detayını döner.
     */
    public function show(Site $site): SiteResource
    {
        abort_unless($site->status === SiteStatus::Active, 404);

        $listing = $site->articleListing()
            ->where('status', SiteStatus::Active)
            ->first();

        abort_unless($listing !== null, 404);

        $site->loadMissing('category');
        $site->applyListingPricing($listing);

        return new SiteResource($site);
    }
}
