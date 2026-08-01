<?php

namespace App\Http\Controllers\Account;

use App\Enums\ContentMode;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderGroup;
use App\Services\OrderContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountOrderContentController extends Controller
{
    public function __construct(
        protected OrderContentService $contents,
    ) {}

    public function __invoke(Request $request, OrderGroup $orderGroup, Order $order): RedirectResponse
    {
        abort_unless((int) $orderGroup->user_id === (int) $request->user()->id, 403);
        abort_unless((int) $order->order_group_id === (int) $orderGroup->id, 404);
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);

        $needsMode = in_array($order->product_type, [
            ProductType::SiteArticle,
            ProductType::PressRelease,
            ProductType::Bundle,
        ], true);

        $data = $request->validate([
            'content_mode' => [Rule::requiredIf($needsMode), 'nullable', Rule::enum(ContentMode::class)],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'brief' => ['nullable', 'string', 'max:5000'],
            'note' => ['nullable', 'string', 'max:2000'],
            'publish_at' => ['nullable', 'date'],
            'article_word_package_id' => [
                'nullable',
                'integer',
                Rule::requiredIf(fn (): bool => $request->input('content_mode') === ContentMode::AiArticle->value),
                'exists:article_word_packages,id',
            ],
            'file' => ['nullable', 'file', 'mimes:doc,docx,pdf,txt,rtf', 'max:10240'],
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:20480'],
            'site_address' => ['nullable', 'url', 'max:2048'],
            'seo_keywords' => ['nullable', 'json'],
        ]);

        if (in_array($order->product_type, [ProductType::SeoPackage, ProductType::BacklinkPackage], true)) {
            $data['keywords'] = $this->parseSeoKeywords($data['seo_keywords'] ?? null);
        }

        $this->contents->update($order, $data);

        return redirect()
            ->route('account.orders.show', $orderGroup)
            ->with('status', 'Ürün yapılandırması kaydedildi.');
    }

    /**
     * @return array<int, array{word: string, target_url: string|null}>
     */
    protected function parseSeoKeywords(?string $json): array
    {
        if (blank($json)) {
            return [];
        }

        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return [];
        }

        $keywords = [];

        foreach ($decoded as $entry) {
            $word = trim((string) ($entry['word'] ?? ''));

            if ($word === '') {
                continue;
            }

            $targetUrl = trim((string) ($entry['target_url'] ?? ''));

            $keywords[] = [
                'word' => mb_substr($word, 0, 100),
                'target_url' => $targetUrl !== '' ? mb_substr($targetUrl, 0, 500) : null,
            ];
        }

        return $keywords;
    }
}
