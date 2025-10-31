<?php

namespace App\Filament\Pages;

use App\Models\CalendarEvent;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LaunchCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Launch Calendar';

    protected static ?string $navigationGroup = 'Main';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.launch-calendar';

    public $eventCompanyName = '';
    public $eventTitle = '';
    public $eventDate = '';
    public $eventNotes = '';
    public $selectedEvent = null;
    public $showEventModal = false;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createEvent')
                ->label('Create Event')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->action(function () {
                    $this->showEventModal = true;
                }),
        ];
    }

    public function mount(): void
    {
        $this->eventDate = now()->format('Y-m-d');
    }

    public function getEventsProperty()
    {
        return CalendarEvent::orderBy('event_date')->get();
    }

    public function createEvent(): void
    {
        $this->validate([
            'eventCompanyName' => 'required|string|max:255',
            'eventTitle' => 'required|string|max:255',
            'eventDate' => 'required|date',
            'eventNotes' => 'nullable|string',
        ]);

        CalendarEvent::create([
            'company_name' => $this->eventCompanyName,
            'event_title' => $this->eventTitle,
            'event_date' => $this->eventDate,
            'notes' => $this->eventNotes,
            'attachments' => null,
        ]);

        $this->resetEventForm();

        Notification::make()
            ->title('Event created successfully')
            ->success()
            ->send();
    }

    public function viewEvent(int $eventId): void
    {
        $this->selectedEvent = CalendarEvent::find($eventId);
        $this->showEventModal = true;
    }

    public function resetEventForm(): void
    {
        $this->eventCompanyName = '';
        $this->eventTitle = '';
        $this->eventDate = now()->format('Y-m-d');
        $this->eventNotes = '';
        $this->selectedEvent = null;
        $this->showEventModal = false;
    }

    public function getEventsJsonProperty()
    {
        return $this->events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->event_title . ' - ' . $event->company_name,
                'start' => $event->event_date->format('Y-m-d'),
            ];
        })->toJson();
    }
}
