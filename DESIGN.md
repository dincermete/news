---
name: Tanıtım Yazısı
description: Türkiye'nin haber/blog sitesi tanıtım pazaryeri — sakin, kararlı bir "yayın kontrol odası" hissi
colors:
  signal-red: "#ff3738"
  deep-navy: "#2248ab"
  ink: "#0a0b0b"
  ink-2: "#545454"
  ink-3: "#a4a4a4"
  paper: "#f8f9fa"
  paper-2: "#eef0f2"
  card-dark: "#101114"
  panel: "#05070c"
typography:
  display:
    fontFamily: "Stack Sans Headline, Inter Display, ui-sans-serif, system-ui, sans-serif"
    fontWeight: 500
    letterSpacing: "-0.02em"
  body:
    fontFamily: "Inter Display, Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    lineHeight: 1.5
rounded:
  sm: "0.5rem"
  md: "0.75rem"
  lg: "1rem"
  xl: "1.25rem"
  2xl: "1.5rem"
  3xl: "2rem"
  full: "9999px"
spacing:
  xs: "0.5rem"
  sm: "0.75rem"
  md: "1rem"
  lg: "1.5rem"
  xl: "2rem"
components:
  button-primary:
    backgroundColor: "{colors.ink}"
    textColor: "#ffffff"
    rounded: "{rounded.full}"
    padding: "10px 20px"
  button-primary-hover:
    backgroundColor: "#000000"
  button-accent:
    backgroundColor: "{colors.deep-navy}"
    textColor: "#ffffff"
    rounded: "{rounded.full}"
    padding: "10px 20px"
  chip-brand:
    backgroundColor: "{colors.signal-red}"
    textColor: "#ffffff"
    rounded: "{rounded.full}"
    padding: "2px 10px"
---

# Design System: Tanıtım Yazısı

## Overview

**Creative North Star: "The Newsroom Control Panel" (Yayın Kontrol Odası)**

Tanıtım Yazısı, yüzlerce haber ve blog sitesini tek bir pazaryerinde toplayan bir aracı platform. Görsel dili, bu işi yürüten kişinin masasındaki sakin ama kararlı kumanda paneli hissini taşır: nötr, güven veren bir zemin (paper/ink) üzerinde, sinyal kırmızısı yalnızca "şimdi harekete geç" anlarında (CTA, bildirim rozeti, aktif durum) yanar — bir yayın stüdyosunun "ON AIR" lambası gibi nadir ve vurucu. Derin lacivert ise arka planda süren güven/kurumsallık hissini taşır.

Sistem, jenerik bir SaaS şablonu gibi görünmeyi reddeder: renk kısıtlı ve amaçlı kullanılır, yüzeyler varsayılan olarak düzdür, gölge yalnızca bir şey "yükseldiğinde" (dropdown, hover, yüzen rozet) devreye girer. Amaç parlaklık değil, güvenilirlik — bir yayıncılık altyapısının ciddiyeti ile bir pazaryerinin davetkârlığı arasında durur.

**Key Characteristics:**
- Nötr zemin (paper/beyaz), rengin nadiren ama kararlı biçimde kullanıldığı kompozisyon
- Durumsal derinlik: düz yüzeyler, gölge yalnızca etkileşim/yükselme anında
- Tam yuvarlak (`rounded-full`) pill butonlar ve ikon çipleri; kartlarda büyük radius (`rounded-2xl`/`3xl`)
- `panel-light`/`panel-dark`: köşeleri yuvarlatılmış, kırmızı→lacivert radyal ışımalı "vitrin panel" düzeni (hero, footer)
- Archivo/Stack Sans başlık + Inter Display gövde ikilisi

## Colors

Kısıtlı, amaçlı bir palet: nötr zemin baskındır, renk yalnızca dikkat çekmesi gereken yerde kullanılır.

### Primary
- **Sinyal Kırmızısı** (`#ff3738`, token `brand-500`): CTA vurguları, bildirim rozetleri, aktif/"yeni" işaretleri, hover glow. **The Rarity Rule.** Herhangi bir ekranın ≤%10'unda kullanılır; azlığı onu güçlü kılar. Asla büyük zemin rengi olarak kullanılmaz.

### Secondary
- **Derin Lacivert** (`#2248ab`, token `accent-600`): ikincil CTA'lar, logo rozeti gradyanı, kategori/istatistik vurguları, linkler. Kırmızıdan daha sık kullanılabilir ama yine de zemin rengi değildir.

### Neutral
- **Ink** (`#0a0b0b`): birincil metin, birincil buton zemini (siyah gradyan `from-black to-[#363b3c]`).
- **Ink-2** (`#545454`): ikincil metin/gövde.
- **Ink-3** (`#a4a4a4`): üçüncül metin, placeholder, meta bilgi.
- **Paper** (`#f8f9fa`) / **Paper-2** (`#eef0f2`): sayfa zemini, kart zemini, panel-light zemini.
- **Card Dark** (`#101114`) / **Panel** (`#05070c`): koyu panel zemini (yalnızca `panel-dark`, hero/footer köşe dekorasyonu — genel header/gövde/dropdown için değil; koyu mega-menü denendi, kullanıcı açık zemini tercih etti).

### Named Rules
**The Newsroom Signal Rule.** Kırmızı bir "canlı/aksiyon gerekiyor" sinyalidir (sepet rozeti, bildirim noktası, aktif nav durumu, birincil CTA vurgusu). Dekoratif olarak kullanılmaz.

## Typography

**Display Font:** Stack Sans Headline (fallback: Inter Display, system-ui)
**Body Font:** Inter Display (fallback: Inter, system-ui)

**Character:** Başlıklar orta ağırlıkta (500), sıkı tracking (-0.02em) ile kesin ve modern; gövde metni standart Inter okunabilirliğiyle sakin kalır. İkisi arasında dramatik bir kontrast yok — kumanda paneli hissi, tipografik gösterişten değil kompozisyondan gelir.

### Hierarchy
- **Display** (500, `text-[2rem]`–`text-5xl`, leading-[1.2]): Hero başlıkları, bölüm başlıkları.
- **Title** (600, `text-lg`–`text-xl`): Kart/panel başlıkları, marka adı.
- **Body** (500, `text-sm`/`text-[13px]`, leading-relaxed): Gövde metni, nav linkleri.
- **Label** (600, `text-[10px]`–`text-[11px]`, tracking-[0.12em]–[0.14em], uppercase): Eyebrow etiketler, bölüm üst başlıkları, kategori etiketleri.

## Layout

`max-w-7xl` merkezi konteyner, `px-4 sm:px-6 lg:px-8` yatay boşluk. Header iki katmanlı: koyu ince bir "utility bar" (`bg-ink`, yalnızca `sm+`) üstte, asıl nav (`bg-white/90 backdrop-blur-md`) altta, sticky. **The Breathing Room Rule.** Masaüstü tam mega-menü (logo + 4 açılır grup + arama/bildirim/hesap/sepet) yalnızca `xl` (1280px) ve üzerinde gösterilir; altında hamburger menüsüne düşer — dar/orta genişlikte sıkışma yerine net bir mobil-tarzı menüye geçiş tercih edilir. Mega-menü panelleri header genişliğinde (`inset-x-0`), sol tarafta link grupları + sağda tek bir "feature" vurgu kartı şeklinde ikiye bölünür.

## Elevation & Depth

Durumsal derinlik sistemi: yüzeyler varsayılan olarak düzdür (`border border-ink/10`, gölgesiz). Gölge yalnızca bir öğe "yükseldiğinde" belirginleşir — dropdown/mega-menü açılışı, kart hover'ı, yüzen rozet (`float-badge`), arama/bildirim popover'ı.

### Shadow Vocabulary
- **soft** (`0 1px 2px rgb(15 18 28/.04), 0 4px 14px rgb(15 18 28/.06)`): Hafif kalkış — rozet, küçük pill.
- **pop** (`0 18px 40px rgb(15 18 28/.08), 0 4px 12px rgb(15 18 28/.04)`): Dropdown'lar, popover'lar, mega-menü paneli — sistemin "aktif yükselme" gölgesi.
- **glow** (`0 20px 60px -18px rgb(34 72 171/.45)`): Lacivert ışıma, dekoratif panel arka planı.
- **glow-brand** (`0 20px 60px -18px rgb(255 55 56/.45)`): Kırmızı ışıma, dekoratif panel arka planı / güçlü CTA hover'ı.

### Named Rules
**The Rest-Flat Rule.** Etkileşimde olmayan hiçbir yüzey gölge taşımaz; gölge her zaman bir duruma (hover/açık/yüzen) tepkidir.

## Shapes

Tam yuvarlak (`rounded-full`) pill: nav linkleri, arama/CTA butonları, ikon çipleri, sepet butonu. Büyük radius (`rounded-2xl` 1.5rem / `rounded-3xl` 2rem): kartlar, dropdown panelleri, hero paneli (`panel-light`/`panel-dark`), mobil çekmece. Kenarlıklar her zaman çok hafif (`border-ink/5` – `border-ink/10`), asla ağır/koyu.

## Components

### Buttons
- **Shape:** `rounded-full` (9999px).
- **Primary:** Siyah gradyan zemin (`bg-gradient-to-b from-black to-[#363b3c]`) veya düz `bg-ink`, beyaz metin, `px-4–5 py-2.5`, ikon+metin.
- **Accent (ikincil CTA):** Lacivert zemin (`accent-600`) veya lacivert metin/ikon üzerinde şeffaf zemin.
- **Hover/Focus:** `hover:scale-[1.03] active:scale-[0.98]`, zeminde hafif koyulaşma (`hover:bg-black`).
- **Ghost/Nav pill:** Şeffaf zemin, `hover:bg-ink/5`; aktif durumda dolu `bg-ink text-white` **veya** (nav bağlamına göre) marka renginin %10 tonuyla vurgulanmış zemin — bkz. Navigation.

### Chips (rozet)
- **Style:** `rounded-full`, `bg-brand-500 text-white`, küçük (`text-[10px]`, `px-2.5 py-0.5`). Sayaç/bildirim rozetleri.
- **State:** Yalnızca "yeni/okunmamış/aktif" durumunda görünür; sıfır durumunda gizlenir.

### Cards / Containers
- **Corner Style:** `rounded-2xl` (kart), `rounded-3xl` (panel/hero).
- **Background:** `bg-white` veya `bg-paper`, dropdown/panel içi alt-kartlar `bg-white ring-1 ring-ink/5`.
- **Shadow Strategy:** Rest'te gölgesiz; `shadow-pop` yalnızca popover/dropdown/hover'da.
- **Border:** `border border-ink/10` (dış), `ring-1 ring-ink/5` (iç alt-kart).
- **Internal Padding:** `p-5`/`p-6` (panel), `p-3.5`/`p-4` (kart).

### Inputs / Fields
- **Style:** Kenarlıksız görünüm, `rounded-full` çevreleyen kapsül (arama kutusu `border border-ink/10 bg-white`), içte `border-0 bg-transparent`.
- **Focus:** `focus:ring-0` — kapsayıcı kapsülün kendisi odak göstergesi, iç input sade kalır.

### Navigation
- **Style:** İki katman — üstte koyu (`bg-ink`) ince utility bar (telefon/kurumsal linkler, `text-white/70`), altta `bg-white/90 backdrop-blur-md` ana nav.
- **Typography:** `text-[13px] font-medium` nav pill, `text-sm` dropdown öğeleri.
- **Default/Hover/Active:** Varsayılan `text-ink-2`; hover `hover:bg-ink/5 hover:text-ink`; aktif `bg-ink/5 text-ink` (bugünkü hal) — **The Newsroom Signal Rule** gereği canlı/aktif durumlar markanın renkleriyle (kırmızı/lacivert) daha güçlü işaretlenmeli, salt nötr gri tonuyla değil.
- **Mobile treatment:** `xl` altında tam ekran yüksekliğinde sağdan açılan çekmece (`w-[min(100vw-2.5rem,22rem)]`), koyu (`bg-ink`) başlık şeridi, kategori grupları.

### Mega-Menü (Signature Component)
Masaüstü nav gruplarının (Siteler/Paketler/Hizmetler/Araçlar) **hover** ile açılan (paylaşılan `openGroup` durumu — aynı anda tek panel açık, gruplar arası geçiş anında/tek panel takas edilerek olur, gecikme yalnızca menü alanının tamamen terk edilmesinde 100ms), tetikleyici butonun **tam ortasına** hizalanan (`start-1/2 -translate-x-1/2`), içerik-boyutlu (24–46rem, header genişliğinde değil) açık panel: `rounded-lg border border-ink/10 bg-white shadow-pop`. Giriş/çıkışta kısa (150ms/100ms) opacity+translateY geçişi — "laglı" hissi önlemek için tek panel prensibi (aşağıya bakınız) ve hızlı easing esastır. İçerik satırları ikon çipi (`bg-paper`, hover `bg-ink`/`text-white`) + kalın başlık + `text-ink-3` iki satırlık açıklama; aktif/güncel öğe `bg-brand-500` ikon çipi + `bg-brand-50` satır zeminiyle işaretlenir (Newsroom Signal Rule). Panelin altında, ana içerikten `border-t border-ink/10` ile ayrılan, 2 sütunlu (`divide-x divide-ink/10`, `bg-paper`) sabit bir footer aksiyon çubuğu bulunur (bir keşif linki + bir dönüşüm linki, ör. "Tüm Siteleri Gör" / "Siteni Ücretsiz Ekle"). **The Single Feature Rule** yerini **The Two-Action Footer Rule**'a bıraktı: panel gövdesinde rekabet eden çoklu CTA kartı yok, yalnızca footer'da iki sade link. **The One Panel Rule.** Herhangi bir anda en fazla bir mega-menü paneli açık olabilir (paylaşılan durum); komşu gruplar arası çift-panel görünmesi/lag hissi bug'dır.

## Do's and Don'ts

### Do:
- **Do** rengi (kırmızı/lacivert) yalnızca aksiyon/durum sinyali için kullan — CTA, rozet, aktif nav durumu, hover glow.
- **Do** yüzeyleri rest halinde düz tut; gölgeyi yalnızca dropdown/hover/yüzen öğede göster (`shadow-pop`).
- **Do** `rounded-full` pilli, geniş kenar boşluklu kompozisyon kullan — sıkışık, bitişik öğelerden kaçın.
- **Do** masaüstü tam menüyü yalnızca yeterli genişlik (`xl`, 1280px+) olduğunda göster; altında hamburger'e düş.

### Don't:
- **Don't** rengi geniş zemin/arka plan rengi olarak kullanma — yalnızca vurgu.
- **Don't** koyu temayı (`panel-dark`, `card-dark`) sayfa gövdesine veya header'ın üst çubuğuna uygulama; dekoratif panel köşeleri ve mega-menü flyout paneliyle sınırlı kalır.
- **Don't** jenerik SaaS şablonu hissi veren nötr gri-mavi, kimliksiz kompozisyonlar üretme — her yüzeyde marka rengi bir "neden bu site" işareti taşımalı.
- **Don't** bir dropdown/mega-menüye birden fazla rekabet eden CTA/feature kartı ekleme.
