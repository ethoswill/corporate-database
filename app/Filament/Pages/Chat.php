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

    protected static ?string $navigationLabel = 'Chat';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.chat';

    public $activeTab = 'active';
    public $selectedTicket = null;
    public $message = '';
    public $ticketSubject = '';
    public $showCreateForm = false;
    public $attachment = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createTicket')
                ->label('New Chat')
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
        $this->activeTab = 'active';
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
        
        // Mark as awaiting reply when opened to remove unread/bold effect
        if ($this->selectedTicket && $this->selectedTicket->status === 'unread') {
            $this->selectedTicket->markAsAwaitingReply();
        }
    }

    public function markAsUnread(int $ticketId): void
    {
        $ticket = Ticket::findOrFail($ticketId);
        $ticket->markAsUnread();

        Notification::make()
            ->title('Marked as unread')
            ->success()
            ->send();

        // If the ticket is selected, refresh it
        if ($this->selectedTicket && $this->selectedTicket->id === $ticketId) {
            $this->selectedTicket->refresh();
        }
    }

    public function resetCreateForm(): void
    {
        $this->ticketSubject = '';
        $this->showCreateForm = false;
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
            'status' => 'unread',
            'last_message_at' => now(),
        ]);

        // Reset form
        $this->resetCreateForm();

        Notification::make()
            ->title('Chat started successfully')
            ->success()
            ->send();

        // Select the newly created ticket
        $this->selectTicket($ticket->id);
        $this->activeTab = 'active';
    }

    public function getTicketsProperty()
    {
        if ($this->activeTab === 'archived') {
            return Ticket::with(['user', 'latestMessage.user'])
                ->archived()
                ->latest('last_message_at')
                ->get();
        }

        // Active tab shows both unread and awaiting_reply
        return Ticket::with(['user', 'latestMessage.user'])
            ->whereIn('status', ['unread', 'awaiting_reply'])
            ->latest('last_message_at')
            ->get();
    }

    public function getUnreadCountProperty()
    {
        return Ticket::where('status', 'unread')->count();
    }

    public function toggleTicketStatus(): void
    {
        if (!$this->selectedTicket) {
            return;
        }

        if ($this->selectedTicket->status === 'archived') {
            $this->selectedTicket->markAsUnread();
            Notification::make()
                ->title('Chat marked as unread')
                ->success()
                ->send();
            $this->activeTab = 'active';
        } else {
            $this->selectedTicket->archive();
        Notification::make()
            ->title('Chat archived')
            ->success()
            ->send();
            $this->activeTab = 'archived';
        }

        $this->selectedTicket->refresh();
    }

    public function toggleTicketStatusContext(int $ticketId): void
    {
        $ticket = Ticket::findOrFail($ticketId);

        if ($ticket->status === 'archived') {
            $ticket->markAsUnread();
            Notification::make()
                ->title('Chat marked as unread')
                ->success()
                ->send();
            $this->activeTab = 'active';
        } else {
            $ticket->archive();
        Notification::make()
            ->title('Chat archived')
            ->success()
            ->send();
            $this->activeTab = 'archived';
        }

        // If the ticket is selected, refresh it
        if ($this->selectedTicket && $this->selectedTicket->id === $ticketId) {
            $this->selectedTicket->refresh();
        }
    }

    public function sendMessage(): void
    {
        if (!$this->selectedTicket || (empty(trim($this->message)) && !$this->attachment)) {
            return;
        }

        $data = [
            'ticket_id' => $this->selectedTicket->id,
            'user_id' => auth()->id(),
            'content' => $this->message ?: '',
        ];

        if ($this->attachment) {
            $path = $this->attachment->store('chat-attachments', 'public');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $this->attachment->getClientOriginalName();
            
            // Determine attachment type
            $mimeType = $this->attachment->getMimeType();
            if (str_starts_with($mimeType, 'image/')) {
                $data['attachment_type'] = 'image';
            } else {
                $data['attachment_type'] = 'file';
            }
        }

        Message::create($data);

        // Update ticket's last message timestamp and mark as awaiting reply since we're sending a message
        $this->selectedTicket->update([
            'last_message_at' => now(),
            'status' => 'awaiting_reply'
        ]);

        // Refresh the selected ticket to show the new message
        $this->selectedTicket->refresh();

        // Clear the message input
        $this->message = '';
        $this->attachment = null;

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
            
            // Check if there's a new message from someone else
            $latestMessage = $this->selectedTicket->messages()->latest()->first();
            if ($latestMessage && $latestMessage->user_id !== auth()->id()) {
                // Mark as unread since someone responded to us
                $this->selectedTicket->update(['status' => 'unread']);
                $this->selectedTicket->refresh();
            }
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
            ->title('Chat title updated')
            ->success()
            ->send();
    }
}
