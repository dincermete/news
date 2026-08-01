<?php

namespace Tests\Feature;

use App\Filament\Resources\BankAccounts\Pages\ListBankAccounts;
use App\Models\BankAccount;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BankAccountResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_bank_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        $accounts = BankAccount::factory()->count(2)->create();

        $this->actingAs($admin);

        Livewire::test(ListBankAccounts::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($accounts);
    }

    public function test_admin_can_create_bank_account_from_modal(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        Livewire::test(ListBankAccounts::class)
            ->callAction('create', [
                'name' => 'Ziraat Bankası',
                'account_name' => 'SDX Gıda',
                'iban' => 'TR330006100519786457841326',
                'short_code' => 'ZRT',
                'is_active' => true,
                'sort_order' => 1,
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $this->assertDatabaseHas(BankAccount::class, [
            'name' => 'Ziraat Bankası',
            'short_code' => 'ZRT',
        ]);
    }

    public function test_admin_can_edit_bank_account_from_modal(): void
    {
        $admin = User::factory()->admin()->create();
        $account = BankAccount::factory()->create([
            'name' => 'Eski Banka',
        ]);

        $this->actingAs($admin);

        Livewire::test(ListBankAccounts::class)
            ->callAction(TestAction::make('edit')->table($account), [
                'name' => 'Yeni Banka',
                'account_name' => $account->account_name,
                'iban' => $account->iban,
                'short_code' => $account->short_code,
                'is_active' => true,
                'sort_order' => $account->sort_order,
            ])
            ->assertHasNoActionErrors()
            ->assertNotified();

        $this->assertDatabaseHas(BankAccount::class, [
            'id' => $account->id,
            'name' => 'Yeni Banka',
        ]);
    }
}
