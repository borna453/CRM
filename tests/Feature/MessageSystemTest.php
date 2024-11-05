<?php

use App\Filament\Resources\MessageResource\Pages\ListMessages;
use App\Models\Company;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use App\Notifications\MessageNotification;
use Illuminate\Database\Eloquent\Model;
use function Pest\Livewire\livewire;

it('creates messages correctly', function () {
    Model::withoutEvents(function () {
        livewire(ListMessages::class)
            ->assertActionExists('create')
            ->mountAction('create')
            ->setActionData([
                'recipient' => 'Contact',
                'users' => 1,
                'title' => 'Sample Message',
                'content' => 'Sample Content',
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors();
    });

    $this->assertDatabaseHas(Message::class, [
        'title' => 'Sample Message',
        'content' => 'Sample Content',
    ]);
});

it('creates a message for a single user correctly', function () {

    Model::withoutEvents(function () {
        livewire(ListMessages::class)
        ->mountAction('create')
        ->setActionData([
            'recipient' => 'user',
            'users' => [$this->regularUser->id],
            'title' => 'Message for User',
            'content' => 'This is a test message for a specific user.',
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();
    });

    $this->assertDatabaseHas(Message::class, [
        'title' => 'Message for User',
        'content' => 'This is a test message for a specific user.',
        'recipient_type' => Message::USER,
        'recipient_ids' => json_encode([$this->regularUser->id]),
    ]);

    $this->assertDatabaseHas(Recipient::class, [
        'user_id' => $this->regularUser->id,
    ]);
});

it('creates a message for a company correctly', function () {
    $users = User::factory()->count(3)->create(['company_id' => $this->company->id]);

    foreach ($users as $user) {
        $user->assignRole('user');
    }

    Model::withoutEvents(function () {
        livewire(ListMessages::class)
        ->mountAction('create')
        ->setActionData([
            'recipient' => 'company',
            'company_users' => [$this->company->id],
            'title' => 'Message for Company',
            'content' => 'This is a test message for a company.',
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();
    });

    $this->assertDatabaseHas(Message::class, [
        'title' => 'Message for Company',
        'content' => 'This is a test message for a company.',
        'recipient_type' => Message::COMPANY,
        'recipient_ids' => json_encode([$this->company->id]),
    ]);

    $regularUsers = User::role('user')->where('company_id', $this->company->id)->get();


    foreach ($regularUsers as $user) {
        $this->assertDatabaseHas(Recipient::class, [
            'user_id' => $user->id,
        ]);
    }
});

it('creates a message for all users correctly', function () {
    $users = User::factory()->count(5)->create();

    foreach ($users as $user) {
        $user->assignRole('user');
    }

    Model::withoutEvents(function () {
        livewire(ListMessages::class)
        ->mountAction('create')
        ->setActionData([
            'recipient' => 'all',
            'title' => 'Message for All',
            'content' => 'This is a test message for all users.',
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();
    });

    $this->assertDatabaseHas(Message::class, [
        'title' => 'Message for All',
        'content' => 'This is a test message for all users.',
        'recipient_type' => Message::ALL,
        'recipient_ids' => null,
    ]);

    foreach ($users as $user) {
        $this->assertDatabaseHas(Recipient::class, [
            'user_id' => $user->id,
        ]);
    }
});

it('creates a message for multiple individual users', function () {
    Notification::fake();

    $users = User::factory()->count(3)->create();

    foreach ($users as $user) {
        $user->assignRole('user');
    }

    Model::withoutEvents(function () use ($users) {
        livewire(ListMessages::class)
        ->mountAction('create')
        ->setActionData([
            'recipient' => 'user',
            'users' => $users->pluck('id')->toArray(),
            'title' => 'Message for Multiple Users',
            'content' => 'This is a test message for multiple users.',
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();
    });

    $this->assertDatabaseHas(Message::class, [
        'title' => 'Message for Multiple Users',
        'content' => 'This is a test message for multiple users.',
        'recipient_type' => Message::USER,
        'recipient_ids' => json_encode($users->pluck('id')->toArray()),
    ]);

    Notification::assertSentTo($users, MessageNotification::class);

    foreach ($users as $user) {
        $this->assertDatabaseHas(Recipient::class, [
            'user_id' => $user->id,
        ]);
    }
});

it('creates a message for multiple companies', function () {
    Notification::fake();

    $companies = Company::factory()->count(2)->create();
    $users = collect();

    foreach ($companies as $company) {
        $companyUsers = User::factory()->count(3)->create(['company_id' => $company->id]);
        foreach ($companyUsers as $user) {
            $user->assignRole('user');
        }
        $users = $users->merge($companyUsers);
    }

    Model::withoutEvents(function () use ($companies) {

        livewire(ListMessages::class)
        ->mountAction('create')
        ->setActionData([
            'recipient' => 'company',
            'company_users' => $companies->pluck('id')->toArray(),
            'title' => 'Message for Multiple Companies',
            'content' => 'This is a test message for multiple companies.',
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors();
    });

    $this->assertDatabaseHas(Message::class, [
        'title' => 'Message for Multiple Companies',
        'content' => 'This is a test message for multiple companies.',
        'recipient_type' => Message::COMPANY,
        'recipient_ids' => json_encode($companies->pluck('id')->toArray()),
    ]);

    Notification::assertSentTo($users, MessageNotification::class);

    foreach ($users as $user) {
        $this->assertDatabaseHas(Recipient::class, [
            'user_id' => $user->id,
        ]);
    }
});

