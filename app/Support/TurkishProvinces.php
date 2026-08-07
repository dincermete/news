<?php

namespace App\Support;

/**
 * Canonical list of Turkey's 81 provinces for SEO landings and the map.
 *
 * @phpstan-type ProvinceDefinition array{name: string, slug: string, plate_code: string, name_locative: string}
 */
final class TurkishProvinces
{
    /**
     * @return list<ProvinceDefinition>
     */
    public static function all(): array
    {
        return [
            ['name' => 'Adana', 'slug' => 'adana', 'plate_code' => '01', 'name_locative' => "Adana'da"],
            ['name' => 'Adıyaman', 'slug' => 'adiyaman', 'plate_code' => '02', 'name_locative' => "Adıyaman'da"],
            ['name' => 'Afyonkarahisar', 'slug' => 'afyonkarahisar', 'plate_code' => '03', 'name_locative' => "Afyonkarahisar'da"],
            ['name' => 'Ağrı', 'slug' => 'agri', 'plate_code' => '04', 'name_locative' => "Ağrı'da"],
            ['name' => 'Amasya', 'slug' => 'amasya', 'plate_code' => '05', 'name_locative' => "Amasya'da"],
            ['name' => 'Ankara', 'slug' => 'ankara', 'plate_code' => '06', 'name_locative' => "Ankara'da"],
            ['name' => 'Antalya', 'slug' => 'antalya', 'plate_code' => '07', 'name_locative' => "Antalya'da"],
            ['name' => 'Artvin', 'slug' => 'artvin', 'plate_code' => '08', 'name_locative' => "Artvin'de"],
            ['name' => 'Aydın', 'slug' => 'aydin', 'plate_code' => '09', 'name_locative' => "Aydın'da"],
            ['name' => 'Balıkesir', 'slug' => 'balikesir', 'plate_code' => '10', 'name_locative' => "Balıkesir'de"],
            ['name' => 'Bilecik', 'slug' => 'bilecik', 'plate_code' => '11', 'name_locative' => "Bilecik'te"],
            ['name' => 'Bingöl', 'slug' => 'bingol', 'plate_code' => '12', 'name_locative' => "Bingöl'de"],
            ['name' => 'Bitlis', 'slug' => 'bitlis', 'plate_code' => '13', 'name_locative' => "Bitlis'te"],
            ['name' => 'Bolu', 'slug' => 'bolu', 'plate_code' => '14', 'name_locative' => "Bolu'da"],
            ['name' => 'Burdur', 'slug' => 'burdur', 'plate_code' => '15', 'name_locative' => "Burdur'da"],
            ['name' => 'Bursa', 'slug' => 'bursa', 'plate_code' => '16', 'name_locative' => "Bursa'da"],
            ['name' => 'Çanakkale', 'slug' => 'canakkale', 'plate_code' => '17', 'name_locative' => "Çanakkale'de"],
            ['name' => 'Çankırı', 'slug' => 'cankiri', 'plate_code' => '18', 'name_locative' => "Çankırı'da"],
            ['name' => 'Çorum', 'slug' => 'corum', 'plate_code' => '19', 'name_locative' => "Çorum'da"],
            ['name' => 'Denizli', 'slug' => 'denizli', 'plate_code' => '20', 'name_locative' => "Denizli'de"],
            ['name' => 'Diyarbakır', 'slug' => 'diyarbakir', 'plate_code' => '21', 'name_locative' => "Diyarbakır'da"],
            ['name' => 'Edirne', 'slug' => 'edirne', 'plate_code' => '22', 'name_locative' => "Edirne'de"],
            ['name' => 'Elazığ', 'slug' => 'elazig', 'plate_code' => '23', 'name_locative' => "Elazığ'da"],
            ['name' => 'Erzincan', 'slug' => 'erzincan', 'plate_code' => '24', 'name_locative' => "Erzincan'da"],
            ['name' => 'Erzurum', 'slug' => 'erzurum', 'plate_code' => '25', 'name_locative' => "Erzurum'da"],
            ['name' => 'Eskişehir', 'slug' => 'eskisehir', 'plate_code' => '26', 'name_locative' => "Eskişehir'de"],
            ['name' => 'Gaziantep', 'slug' => 'gaziantep', 'plate_code' => '27', 'name_locative' => "Gaziantep'te"],
            ['name' => 'Giresun', 'slug' => 'giresun', 'plate_code' => '28', 'name_locative' => "Giresun'da"],
            ['name' => 'Gümüşhane', 'slug' => 'gumushane', 'plate_code' => '29', 'name_locative' => "Gümüşhane'de"],
            ['name' => 'Hakkâri', 'slug' => 'hakkari', 'plate_code' => '30', 'name_locative' => "Hakkâri'de"],
            ['name' => 'Hatay', 'slug' => 'hatay', 'plate_code' => '31', 'name_locative' => "Hatay'da"],
            ['name' => 'Isparta', 'slug' => 'isparta', 'plate_code' => '32', 'name_locative' => "Isparta'da"],
            ['name' => 'Mersin', 'slug' => 'mersin', 'plate_code' => '33', 'name_locative' => "Mersin'de"],
            ['name' => 'İstanbul', 'slug' => 'istanbul', 'plate_code' => '34', 'name_locative' => "İstanbul'da"],
            ['name' => 'İzmir', 'slug' => 'izmir', 'plate_code' => '35', 'name_locative' => "İzmir'de"],
            ['name' => 'Kars', 'slug' => 'kars', 'plate_code' => '36', 'name_locative' => "Kars'ta"],
            ['name' => 'Kastamonu', 'slug' => 'kastamonu', 'plate_code' => '37', 'name_locative' => "Kastamonu'da"],
            ['name' => 'Kayseri', 'slug' => 'kayseri', 'plate_code' => '38', 'name_locative' => "Kayseri'de"],
            ['name' => 'Kırklareli', 'slug' => 'kirklareli', 'plate_code' => '39', 'name_locative' => "Kırklareli'de"],
            ['name' => 'Kırşehir', 'slug' => 'kirsehir', 'plate_code' => '40', 'name_locative' => "Kırşehir'de"],
            ['name' => 'Kocaeli', 'slug' => 'kocaeli', 'plate_code' => '41', 'name_locative' => "Kocaeli'de"],
            ['name' => 'Konya', 'slug' => 'konya', 'plate_code' => '42', 'name_locative' => "Konya'da"],
            ['name' => 'Kütahya', 'slug' => 'kutahya', 'plate_code' => '43', 'name_locative' => "Kütahya'da"],
            ['name' => 'Malatya', 'slug' => 'malatya', 'plate_code' => '44', 'name_locative' => "Malatya'da"],
            ['name' => 'Manisa', 'slug' => 'manisa', 'plate_code' => '45', 'name_locative' => "Manisa'da"],
            ['name' => 'Kahramanmaraş', 'slug' => 'kahramanmaras', 'plate_code' => '46', 'name_locative' => "Kahramanmaraş'ta"],
            ['name' => 'Mardin', 'slug' => 'mardin', 'plate_code' => '47', 'name_locative' => "Mardin'de"],
            ['name' => 'Muğla', 'slug' => 'mugla', 'plate_code' => '48', 'name_locative' => "Muğla'da"],
            ['name' => 'Muş', 'slug' => 'mus', 'plate_code' => '49', 'name_locative' => "Muş'ta"],
            ['name' => 'Nevşehir', 'slug' => 'nevsehir', 'plate_code' => '50', 'name_locative' => "Nevşehir'de"],
            ['name' => 'Niğde', 'slug' => 'nigde', 'plate_code' => '51', 'name_locative' => "Niğde'de"],
            ['name' => 'Ordu', 'slug' => 'ordu', 'plate_code' => '52', 'name_locative' => "Ordu'da"],
            ['name' => 'Rize', 'slug' => 'rize', 'plate_code' => '53', 'name_locative' => "Rize'de"],
            ['name' => 'Sakarya', 'slug' => 'sakarya', 'plate_code' => '54', 'name_locative' => "Sakarya'da"],
            ['name' => 'Samsun', 'slug' => 'samsun', 'plate_code' => '55', 'name_locative' => "Samsun'da"],
            ['name' => 'Siirt', 'slug' => 'siirt', 'plate_code' => '56', 'name_locative' => "Siirt'te"],
            ['name' => 'Sinop', 'slug' => 'sinop', 'plate_code' => '57', 'name_locative' => "Sinop'ta"],
            ['name' => 'Sivas', 'slug' => 'sivas', 'plate_code' => '58', 'name_locative' => "Sivas'ta"],
            ['name' => 'Tekirdağ', 'slug' => 'tekirdag', 'plate_code' => '59', 'name_locative' => "Tekirdağ'da"],
            ['name' => 'Tokat', 'slug' => 'tokat', 'plate_code' => '60', 'name_locative' => "Tokat'ta"],
            ['name' => 'Trabzon', 'slug' => 'trabzon', 'plate_code' => '61', 'name_locative' => "Trabzon'da"],
            ['name' => 'Tunceli', 'slug' => 'tunceli', 'plate_code' => '62', 'name_locative' => "Tunceli'de"],
            ['name' => 'Şanlıurfa', 'slug' => 'sanliurfa', 'plate_code' => '63', 'name_locative' => "Şanlıurfa'da"],
            ['name' => 'Uşak', 'slug' => 'usak', 'plate_code' => '64', 'name_locative' => "Uşak'ta"],
            ['name' => 'Van', 'slug' => 'van', 'plate_code' => '65', 'name_locative' => "Van'da"],
            ['name' => 'Yozgat', 'slug' => 'yozgat', 'plate_code' => '66', 'name_locative' => "Yozgat'ta"],
            ['name' => 'Zonguldak', 'slug' => 'zonguldak', 'plate_code' => '67', 'name_locative' => "Zonguldak'ta"],
            ['name' => 'Aksaray', 'slug' => 'aksaray', 'plate_code' => '68', 'name_locative' => "Aksaray'da"],
            ['name' => 'Bayburt', 'slug' => 'bayburt', 'plate_code' => '69', 'name_locative' => "Bayburt'ta"],
            ['name' => 'Karaman', 'slug' => 'karaman', 'plate_code' => '70', 'name_locative' => "Karaman'da"],
            ['name' => 'Kırıkkale', 'slug' => 'kirikkale', 'plate_code' => '71', 'name_locative' => "Kırıkkale'de"],
            ['name' => 'Batman', 'slug' => 'batman', 'plate_code' => '72', 'name_locative' => "Batman'da"],
            ['name' => 'Şırnak', 'slug' => 'sirnak', 'plate_code' => '73', 'name_locative' => "Şırnak'ta"],
            ['name' => 'Bartın', 'slug' => 'bartin', 'plate_code' => '74', 'name_locative' => "Bartın'da"],
            ['name' => 'Ardahan', 'slug' => 'ardahan', 'plate_code' => '75', 'name_locative' => "Ardahan'da"],
            ['name' => 'Iğdır', 'slug' => 'igdir', 'plate_code' => '76', 'name_locative' => "Iğdır'da"],
            ['name' => 'Yalova', 'slug' => 'yalova', 'plate_code' => '77', 'name_locative' => "Yalova'da"],
            ['name' => 'Karabük', 'slug' => 'karabuk', 'plate_code' => '78', 'name_locative' => "Karabük'te"],
            ['name' => 'Kilis', 'slug' => 'kilis', 'plate_code' => '79', 'name_locative' => "Kilis'te"],
            ['name' => 'Osmaniye', 'slug' => 'osmaniye', 'plate_code' => '80', 'name_locative' => "Osmaniye'de"],
            ['name' => 'Düzce', 'slug' => 'duzce', 'plate_code' => '81', 'name_locative' => "Düzce'de"],
        ];
    }
}
