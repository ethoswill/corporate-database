<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use App\Models\Message;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Chat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Support Tickets';

    protected static ?string $navigationGroup = 'Main';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.chat';

    public $activeTab = 'open';
    public $selectedTicket = null;
    public $message = '';
    public $ticketSubject = '';
    public $showCreateForm = false;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createTicket')
                ->label('Create Ticket')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->action(function () {
                    $this->showCreateForm = true;
                }),
        ];
    }

    public function mount(): void
    {
        // Reset state
        $this->activeTab = 'open';
        $this->selectedTicket = null;
        $this->showCreateForm = false;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->selectedTicket = null;
    }

    public function selectTicket(int $ticketId): void
    {
        $this->selectedTicket = Ticket::with(['user', 'messages.user', 'location'])->find($ticketId);
    }

    public function createTicket(): void
    {
        $this->validate([
            'ticketSubject' => 'required|string|max:255',
        ]);

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'title' => $this->ticketSubject,
            'description' => null,
            'priority' => 'medium',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        // Reset form
        $this->ticketSubject = '';
        $this->showCreateForm = false;

        Notification::make()
            ->title('Ticket created successfully')
            ->success()
            ->send();

        // Select the newly created ticket
        $this->selectTicket($ticket->id);
        $this->activeTab = 'open';
    }

    public function getTicketsProperty()
    {
        if ($this->activeTab === 'archived') {
            return Ticket::with(['user', 'latestMessage.user'])
                ->archived()
                ->latest('last_message_at')
                ->get();
        }

        return Ticket::with(['user', 'latestMessage.user'])
            ->open()
            ->latest('last_message_at')
            ->get();
    }

    public function toggleTicketStatus(): void
    {
        if (!$this->selectedTicket) {
            return;
        }

        if ($this->selectedTicket->status === 'open') {
            $this->selectedTicket->archive();
            Notification::make()
                ->title('Ticket archived')
                ->success()
                ->send();
            $this->activeTab = 'archived';
        } else {
            $this->selectedTicket->open();
            Notification::make()
                ->title('Ticket opened')
                ->success()
                ->send();
            $this->activeTab = 'open';
        }

        $this->selectedTicket->refresh();
    }

    public function sendMessage(): void
    {
        if (!$this->selectedTicket || empty(trim($this->message))) {
            return;
        }

        Message::create([
            'ticket_id' => $this->selectedTicket->id,
            'user_id' => auth()->id(),
            'content' => $this->message,
        ]);

        // Update ticket's last message timestamp
        $this->selectedTicket->update(['last_message_at' => now()]);

        // Refresh the selected ticket to show the new message
        $this->selectedTicket->refresh();

        // Clear the message input
        $this->message = '';

        // Dispatch browser event to scroll to bottom
        $this->dispatch('message-sent');

        Notification::make()
            ->title('Message sent')
            ->success()
            ->send();
    }

    public function refreshTicket(): void
    {
        if ($this->selectedTicket) {
            $this->selectedTicket->refresh();
            $this->selectedTicket->load(['user', 'messages.user', 'location']);
        }
    }

    public function updateTicketTitle(): void
    {
        if (!$this->selectedTicket) {
            return;
        }

        $this->validate([
            'selectedTicket.title' => 'required|string|max:255',
        ]);

        $this->selectedTicket->save();

        Notification::make()
            ->title('Ticket title updated')
            ->success()
            ->send();
    }
}
