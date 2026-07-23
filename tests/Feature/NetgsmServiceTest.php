<?php

namespace Tests\Feature;

use App\Services\NetgsmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class NetgsmServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_posts_to_netgsm_api_and_returns_true(): void
    {
        config([
            'netgsm.username' => 'user',
            'netgsm.password' => 'secret',
            'netgsm.header' => 'STANITIM',
            'netgsm.send_url' => 'https://api.netgsm.com.tr/sms/rest/v2/send',
            'netgsm.encoding' => 'TR',
        ]);

        Http::fake([
            'https://api.netgsm.com.tr/sms/rest/v2/send' => Http::response([
                'code' => '00',
                'jobid' => '123456',
            ], 200),
        ]);

        $result = app(NetgsmService::class)->send('05321234567', 'Merhaba test');

        $this->assertTrue($result);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.netgsm.com.tr/sms/rest/v2/send'
                && $request['msgheader'] === 'STANITIM'
                && $request['messages'][0]['msg'] === 'Merhaba test'
                && $request['messages'][0]['no'] === '5321234567';
        });
    }

    public function test_send_throws_when_credentials_missing(): void
    {
        config([
            'netgsm.username' => '',
            'netgsm.password' => '',
            'netgsm.header' => '',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Netgsm bilgileri yapılandırılmamış.');

        app(NetgsmService::class)->send('5321234567', 'test');
    }
}
