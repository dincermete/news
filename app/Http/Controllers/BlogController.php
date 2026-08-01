<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\SeoMetaService;
use App\Support\BlogContent;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    public function index(Request $request, SeoMetaService $seo): View
    {
        $q = trim((string) $request->query('q', ''));
        $categorySlug = $request->query('kategori');

        $posts = BlogPost::query()
            ->published()
            ->with(['category', 'author', 'tags'])
            ->when($q !== '', function ($query) use ($q): void {
                $query->where(function ($inner) use ($q): void {
                    $inner->where('title', 'like', "%{$q}%")
                        ->orWhere('excerpt', 'like', "%{$q}%");
                });
            })
            ->when(filled($categorySlug), function ($query) use ($categorySlug): void {
                $query->whereHas('category', fn ($categories) => $categories
                    ->where('slug', $categorySlug)
                    ->active());
            })
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $featured = BlogPost::query()
            ->published()
            ->featured()
            ->with(['category'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $categories = BlogCategory::query()
            ->active()
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('blog.index', [
            'meta' => $seo->forRoute(
                'blog.index',
                'Blog',
                'SEO, backlink ve dijital görünürlük hakkında güncel yazılar.',
            ),
            'posts' => $posts,
            'featured' => $featured,
            'categories' => $categories,
            'q' => $q,
            'categorySlug' => filled($categorySlug) ? (string) $categorySlug : null,
        ]);
    }

    public function show(string $slug, SeoMetaService $seo): View
    {
        $post = BlogPost::query()
            ->published()
            ->where('slug', $slug)
            ->with(['category', 'author', 'tags'])
            ->first();

        if ($post === null) {
            throw new NotFoundHttpException;
        }

        $post->increment('views_count');
        $post->refresh();

        $toc = BlogContent::withTableOfContents($post->content);

        $previous = BlogPost::query()
            ->published()
            ->where('published_at', '<', $post->published_at)
            ->latest('published_at')
            ->first();

        $next = BlogPost::query()
            ->published()
            ->where('published_at', '>', $post->published_at)
            ->oldest('published_at')
            ->first();

        $related = BlogPost::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->when(
                $post->blog_category_id,
                fn ($query) => $query->where('blog_category_id', $post->blog_category_id),
            )
            ->with(['category'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        if ($related->count() < 3) {
            $extra = BlogPost::query()
                ->published()
                ->where('id', '!=', $post->id)
                ->whereNotIn('id', $related->pluck('id'))
                ->with(['category'])
                ->latest('published_at')
                ->limit(3 - $related->count())
                ->get();

            $related = $related->concat($extra)->values();
        }

        $categories = BlogCategory::query()
            ->active()
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $popularTags = BlogTag::query()
            ->whereHas('posts', fn ($query) => $query->published())
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderByDesc('posts_count')
            ->orderBy('name')
            ->limit(16)
            ->get();

        return view('blog.show', [
            'meta' => $seo->forBlogPost($post),
            'post' => $post,
            'contentHtml' => $toc['html'],
            'tocItems' => $toc['items'],
            'previous' => $previous,
            'next' => $next,
            'related' => $related,
            'categories' => $categories,
            'popularTags' => $popularTags,
        ]);
    }

    public function category(string $slug, SeoMetaService $seo): View
    {
        $category = BlogCategory::query()
            ->active()
            ->where('slug', $slug)
            ->first();

        if ($category === null) {
            throw new NotFoundHttpException;
        }

        $posts = BlogPost::query()
            ->published()
            ->where('blog_category_id', $category->id)
            ->with(['category', 'author', 'tags'])
            ->latest('published_at')
            ->paginate(9);

        $categories = BlogCategory::query()
            ->active()
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('blog.index', [
            'meta' => $seo->forBlogCategory($category),
            'posts' => $posts,
            'featured' => collect(),
            'categories' => $categories,
            'q' => '',
            'categorySlug' => $category->slug,
            'activeCategory' => $category,
        ]);
    }

    public function tag(string $slug, SeoMetaService $seo): View
    {
        $tag = BlogTag::query()->where('slug', $slug)->first();

        if ($tag === null) {
            throw new NotFoundHttpException;
        }

        $posts = BlogPost::query()
            ->published()
            ->whereHas('tags', fn ($tags) => $tags->where('blog_tags.id', $tag->id))
            ->with(['category', 'author', 'tags'])
            ->latest('published_at')
            ->paginate(9);

        $categories = BlogCategory::query()
            ->active()
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('blog.index', [
            'meta' => $seo->forBlogTag($tag),
            'posts' => $posts,
            'featured' => collect(),
            'categories' => $categories,
            'q' => '',
            'categorySlug' => null,
            'activeTag' => $tag,
        ]);
    }
}
