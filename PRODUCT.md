# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Primary: advertisers and marketing/PR agencies who want their brand covered on Turkish news and content sites. They come to the public storefront to browse sites and packages, add items to a cart, check out, and track orders through to publication. Agencies typically buy in volume on behalf of their own clients.

Secondary / back-office: publishers and site owners who submit their site as sellable inventory (`SiteSubmission`, `site_owner_*` fields on `Site`). This side is vetted and operated internally rather than designed as a public-facing audience; it is supply-side plumbing, not a storefront persona.

Locale: Turkish-first (`APP_LOCALE=tr`), Europe/Istanbul timezone. Storefront copy, routes, and enum labels are in Turkish.

## Product Purpose

A one-stop marketplace for paid media/content placements that would otherwise require contacting each publisher individually. Buyers purchase:

- **Tanıtım yazısı (site article):** a sponsored/promotional article placed on a partner news or content site.
- **Basın bülteni (press release):** press release distribution.
- **Footer link:** a placed backlink in a site's footer.
- **Backlink packages** and **SEO packages** (keyword-count/monthly-price based) sold independently of a specific site.
- **Site bundles** (tanıtım paketleri): curated multi-site packages.
- **Agency services** (hizmetler) and a **free SEO analysis** lead-gen tool (ücretsiz analiz).

Orders move through a defined content-production lifecycle (`OrderStatus`): payment pending → content pending → review → in queue → published → report sent → completed (or refunded/cancelled), reflecting that most products require content submission and editorial placement, not instant digital delivery.

Success means a buyer can find a suitable site/package by category, price, or metrics; buy it (cash or wallet) without back-and-forth negotiation; and see the order through to a published, reported placement.

## Positioning

Speed and one-stop convenience: instead of sourcing sites, negotiating price per site, and chasing link placement across many separate publisher relationships, a buyer assembles a single cart spanning promotional articles, press releases, backlinks, and SEO packages, and checks out once. Homepage tagline: "Tanıtım Yazınız Haber Sitelerinde Yerini Alsın" ("Your promotional article takes its place on news sites") — "Site aramayı, pazarlığı ve link kovalamayı bırakın. Tanıtım yazısı, basın bülteni ve backlink tek yerde." (Stop searching for sites, negotiating, and chasing links — promotional article, press release, and backlink all in one place.)

## Operating Context

- Sites are vetted and scored on DA/PA/Moz Rank/spam score (with source and last-updated tracking) so buyers can judge site quality before buying.
- Sites are filterable by category (`SiteCategory`) and by province (city-level landing pages, e.g. `/{slug}-tanitim-yazisi-siteleri`), reflecting Turkish local-SEO buying patterns.
- Buyers have an account area (`/hesabim`) with orders, invoices, favorites, wallet top-ups, a spin-wheel loyalty mechanic, an affiliate program, and support tickets.
- Payment supports wallet balance, coupons/discount tiers, and PayTR (Turkish payment gateway) plus a manual bank-transfer notification flow.
- Admin/ops runs through a Filament panel (v5) covering sites, orders, carts, customers, discount tiers, labels, payments, promotional listings, site categories, and more.
- Content and reviews are two-way: buyers can review/question sites, bundles, SEO packages, and backlink packages after interacting with them.

## Capabilities and Constraints

- Stack: Laravel 13, Filament 5, Livewire, Tailwind — existing codebase, not a greenfield choice.
- Prices are per-item in a configurable `Currency` (site/package prices carry a currency field), with discount pricing supported per promotional listing.
- Order lifecycle transitions are enum-enforced (`OrderStatus::allowedTransitions()`), not freeform status text.
- Terminology to preserve in copy and code: "Tanıtım yazısı" (promotional/site article), "Basın bülteni" (press release), "Site" (a publisher property), "Tanıtım paketleri" (site bundles), "Hizmetler" (agency services).

## Brand Commitments

- `APP_NAME` is "Tanıtım Yazısı" — treat this as the working product/brand name unless told otherwise.
- Hosted at `news.sdxgida.com`; the `sdxgida` parent/host relationship has not been confirmed as a brand fact and should not be surfaced as such without confirmation.

## Evidence on Hand

- The homepage hero displays partner logos for Hürriyet, NTV, Mynet, Milliyet, and Sabah. The user confirmed this set is **mixed** — some are real active publisher partners, others are illustrative/placeholder — without specifying which. Future work must not present the full set as confirmed real partnerships; verify before using any specific name as a proof point, and do not add new invented publisher names.
- No testimonials, case studies, or press coverage were confirmed as real; do not fabricate any.

## Product Principles

1. Convenience beats negotiation — every product decision should reduce the number of separate relationships/steps a buyer needs versus going direct to a publisher.
2. Trust signals are metric-based, not claim-based — lean on DA/PA/spam-score and order-status transparency rather than unverifiable marketing claims.
3. The storefront is the product — publisher/site-owner tooling exists to feed inventory but is not the audience design work should optimize for.
4. Turkish-first, locally grounded — copy, terminology, and geography (province-level pages) are native to the Turkish market, not translated afterthoughts.

## Accessibility & Inclusion

No product-specific accessibility requirement has been established; follow standard web accessibility practice absent further direction.
