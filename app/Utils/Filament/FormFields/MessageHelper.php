<?php

namespace App\Utils\Filament\FormFields;

use App\Models\Company;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use App\Notifications\MessageNotification;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Events\DatabaseNotificationsSent;

class MessageHelper
{
    public static function formFields(): array
    {
        return [
            Select::make('recipient')
                ->default('company')
                ->options([
                    'company' => __('portal.companies.company'),
                    'user' => __('portal.users.contact'),
                    'all' => __('portal.all'),
                ])
                ->preload()
                ->reactive()
                ->required()
                ->searchable()
                ->label(__('portal.messages.recipient')),
            Select::make('company_users')
                ->reactive()
                ->multiple()
                ->required()
                ->visible(fn ($get) => $get('recipient') === 'company')
                ->options(Company::all()->pluck('name', 'id'))->label(__('portal.messages.company_users')),
            Select::make('users')
                ->label(__('portal.users.contact'))
                ->reactive()
                ->required()
                ->multiple()
                ->visible(fn ($get) => $get('recipient') === 'user')
                ->options(User::role(User::USER)->pluck('name', 'id'))
                ->label(__('portal.messages.users')),
            TextInput::make('title')
                ->required()
                ->label(__('portal.messages.title')),
            RichEditor::make('content')
                ->required()
                ->label(__('portal.messages.content')),
        ];
    }

    public static function create($data): void
    {
        [$recipientType, $recipientIds] = match ($data['recipient']) {
            'company' => [Message::COMPANY, $data['company_users']],
            'user' => [Message::USER, $data['users']],
            'all' => [Message::ALL, null],
            default => [null, null],
        };

        $message = Message::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'conversation_id' => null,
            'recipient_type' => $recipientType,
            'recipient_ids' => $recipientIds,
        ]);

        self::createRecipients($message);
    }

    private static function createRecipients(Message $message): void
    {
        $recipientsQuery = match ($message->recipient_type) {
            Message::USER => User::whereIn('id', $message->recipient_ids)->isAssignableUser(),
            Message::COMPANY => User::whereIn('company_id', $message->recipient_ids)->isAssignableUser(),
            Message::ALL => User::isAssignableUser(),
            default => null,
        };

        $recipientsQuery?->each(function (User $recipient) use ($message) {
            $recipient->notify(new MessageNotification($message, $recipient));
            Recipient::create([
                'message_id' => $message->id,
                'user_id' => $recipient->id,
            ]);
            event(new DatabaseNotificationsSent($recipient));
        });
    }
}
