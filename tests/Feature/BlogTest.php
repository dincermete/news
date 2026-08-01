<?php

namespace Tests\Feature;

use App\Enums\SiteStatus;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use App\Services\ProductPublicUrl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_lists_published_posts_only(): void
    {
        $published = BlogPost::factory()->published()->create([
            'title' => 'Yayında Olan Yazı',
            'slug' => 'yayinda-olan-yazi',
        ]);
        BlogPost::factory()->draft()->create([
            'title' => 'Taslak Yazı',
            'slug' => 'taslak-yazi',
        ]);
        BlogPost::factory()->create([
            'title' => 'Gelecek Yazı',
            'slug' => 'gelecek-yazi',
            'status' => SiteStatus::Active,
            'published_at' => now()->addDay(),
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('Yayında Olan Yazı', false);
        $response->assertDontSee('Taslak Yazı', false);
        $response->assertDontSee('Gelecek Yazı', false);
        $response->assertSee('canonical', false);
        $this->assertTrue($published->isPublished());
    }

    public function test_blog_show_renders_seo_and_json_ld(): void
    {
        $author = User::factory()->create(['name' => 'Editör Ada']);
        $post = BlogPost::factory()->published()->create([
            'user_id' => $author->id,
            'title' => 'SEO Rehberi',
            'slug' => 'seo-rehberi',
            'excerpt' => 'Kısa özet metni burada.',
            'meta_title' => 'SEO Rehberi Meta',
            'meta_description' => 'Meta açıklama blog için.',
            'content' => '<p>Blog içeriği paragrafı.</p>',
        ]);

        $response = $this->get(route('blog.show', $post));

        $response->assertOk();
        $response->assertSee('SEO Rehberi Meta', false);
        $response->assertSee('Meta açıklama blog için.', false);
        $response->assertSee('BlogPosting', false);
        $response->assertSee('Blog içeriği paragrafı.', false);
        $response->assertSee('Editör Ada', false);

        $post->refresh();
        $this->assertSame(1, $post->views_count);
    }

    public function test_blog_show_renders_toc_sidebar_and_related_nav(): void
    {
        $category = BlogCategory::factory()->create([
            'name' => 'SEO',
            'slug' => 'seo',
        ]);
        $tag = BlogTag::factory()->create([
            'name' => 'Backlink',
            'slug' => 'backlink',
        ]);

        $previous = BlogPost::factory()->published()->create([
            'title' => 'Önceki Yazı Başlığı',
            'slug' => 'onceki-yazi',
            'published_at' => now()->subDays(2),
        ]);

        $post = BlogPost::factory()->published()->create([
            'blog_category_id' => $category->id,
            'title' => 'TOC Yazısı',
            'slug' => 'toc-yazisi',
            'published_at' => now()->subDay(),
            'content' => '<h2>Giriş Bölümü</h2><p>Paragraf</p><h3>Detay</h3><p>Devam</p>',
        ]);
        $post->tags()->attach($tag);

        $next = BlogPost::factory()->published()->create([
            'title' => 'Sonraki Yazı Başlığı',
            'slug' => 'sonraki-yazi',
            'published_at' => now(),
        ]);

        $response = $this->get(route('blog.show', $post));

        $response->assertOk();
        $response->assertSee('İçindekiler', false);
        $response->assertSee('Giriş Bölümü', false);
        $response->assertSee('id="giris-bolumu"', false);
        $response->assertSee('Kategoriler', false);
        $response->assertSee('Popüler etiketler', false);
        $response->assertSee('Önceki yazı', false);
        $response->assertSee('Önceki Yazı Başlığı', false);
        $response->assertSee('Sonraki yazı', false);
        $response->assertSee('Sonraki Yazı Başlığı', false);
        $response->assertSee('Bu yazıları da beğenebilirsiniz', false);
        $this->assertNotNull($previous->id);
        $this->assertNotNull($next->id);
    }

    public function test_draft_post_is_not_publicly_accessible(): void
    {
        $post = BlogPost::factory()->draft()->create([
            'slug' => 'gizli-taslak',
        ]);

        $this->get(route('blog.show', $post))->assertNotFound();
    }

    public function test_category_and_tag_pages_filter_posts(): void
    {
        $category = BlogCategory::factory()->create([
            'name' => 'SEO',
            'slug' => 'seo',
        ]);
        $other = BlogCategory::factory()->create([
            'name' => 'Haber',
            'slug' => 'haber',
        ]);
        $tag = BlogTag::factory()->create([
            'name' => 'Backlink',
            'slug' => 'backlink',
        ]);

        $matched = BlogPost::factory()->published()->create([
            'blog_category_id' => $category->id,
            'title' => 'Kategori Yazısı',
            'slug' => 'kategori-yazisi',
        ]);
        $matched->tags()->attach($tag);

        BlogPost::factory()->published()->create([
            'blog_category_id' => $other->id,
            'title' => 'Diğer Kategori',
            'slug' => 'diger-kategori',
        ]);

        $this->get(route('blog.category', $category))
            ->assertOk()
            ->assertSee('Kategori Yazısı', false)
            ->assertDontSee('Diğer Kategori', false);

        $this->get(route('blog.tag', $tag))
            ->assertOk()
            ->assertSee('Kategori Yazısı', false)
            ->assertDontSee('Diğer Kategori', false);

        $this->assertTrue($matched->tags->contains('id', $tag->id));
    }

    public function test_blog_is_reserved_public_path(): void
    {
        $this->assertContains(
            'blog',
            app(ProductPublicUrl::class)->reservedPaths(),
        );
    }
}
