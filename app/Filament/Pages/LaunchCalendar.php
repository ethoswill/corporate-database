<?php

namespace App\Filament\Pages;

use App\Models\CalendarEvent;
use App\Models\EventTask;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class LaunchCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Events';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.launch-calendar';

    public $companyName = '';
    public $eventTitle = '';
    public $eventDate = '';
    public $eventNotes = '';
    public $attachment = null;
    public $showCreateModal = false;
    public $editingEventId = null;
    public $viewingEventId = null;
    
    // Filters
    public $filterCompany = null;
    
    // Calendar navigation
    public $currentMonth = null;
    public $currentYear = null;
    
    // View mode: 'calendar' or 'cards'
    public $viewMode = 'cards';
    
    // Task management
    public $taskTitle = '';
    public $taskDescription = '';
    public $taskDueDate = '';
    public $taskAssignedTo = null;
    public $showTaskModal = false;
    public $managingTasksForEventId = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createEvent')
                ->label('Add Event')
                ->icon('heroicon-o-plus')
                ->color('success')
                ->action(function () {
                    $this->showCreateModal = true;
                }),
        ];
    }

    public function mount(): void
    {
        $this->eventDate = now()->format('Y-m-d');
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }
    
    public function previousMonth(): void
    {
        if ($this->currentMonth == 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
    }
    
    public function nextMonth(): void
    {
        if ($this->currentMonth == 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
    }
    
    public function goToToday(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    public function getEventsProperty()
    {
        return CalendarEvent::query()
            ->with('tasks.assignedTo')
            ->when($this->filterCompany, function ($query) {
                $query->where('company_name', $this->filterCompany);
            })
            ->orderBy('event_date', 'asc')
            ->get();
    }
    
    public function getCompaniesProperty()
    {
        return CalendarEvent::distinct()
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->orderBy('company_name')
            ->pluck('company_name')
            ->filter()
            ->values();
    }
    
    public function clearFilters(): void
    {
        $this->filterCompany = null;
    }

    public function createEvent(): void
    {
        $this->validate([
            'companyName' => 'required|string|max:255',
            'eventTitle' => 'required|string|max:255',
            'eventDate' => 'required|date',
            'eventNotes' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $data = [
            'company_name' => $this->companyName,
            'event_title' => $this->eventTitle,
            'event_date' => $this->eventDate,
            'notes' => $this->eventNotes,
        ];

        if ($this->attachment) {
            $path = $this->attachment->store('calendar-attachments', 'public');
            $data['attachment_path'] = $path;
            $data['attachment_name'] = $this->attachment->getClientOriginalName();
        }

        if ($this->editingEventId) {
            $event = CalendarEvent::findOrFail($this->editingEventId);
            
            // Only update attachment if a new one is uploaded
            if ($this->attachment && $event->attachment_path) {
                \Storage::disk('public')->delete($event->attachment_path);
            }
            
            $event->update($data);
            
            Notification::make()
                ->title('Event updated successfully')
                ->success()
                ->send();
        } else {
            CalendarEvent::create($data);
            
            Notification::make()
                ->title('Event created successfully')
                ->success()
                ->send();
        }

        $this->resetEventForm();
    }

    public function resetEventForm(): void
    {
        $this->companyName = '';
        $this->eventTitle = '';
        $this->eventDate = now()->format('Y-m-d');
        $this->eventNotes = '';
        $this->attachment = null;
        $this->editingEventId = null;
        $this->showCreateModal = false;
    }

    public function editEvent(int $eventId): void
    {
        $event = CalendarEvent::findOrFail($eventId);
        
        $this->editingEventId = $eventId;
        $this->companyName = $event->company_name;
        $this->eventTitle = $event->event_title;
        $this->eventDate = $event->event_date->format('Y-m-d');
        $this->eventNotes = $event->notes ?? '';
        $this->attachment = null;
        $this->showCreateModal = true;
    }

    public function duplicateEvent(int $eventId): void
    {
        $event = CalendarEvent::findOrFail($eventId);
        
        $this->companyName = $event->company_name;
        $this->eventTitle = $event->event_title . ' (Copy)';
        $this->eventDate = $event->event_date->format('Y-m-d');
        $this->eventNotes = $event->notes ?? '';
        $this->attachment = null;
        $this->showCreateModal = true;
    }

    public function deleteEvent(int $eventId): void
    {
        $event = CalendarEvent::findOrFail($eventId);
        
        if ($event->attachment_path) {
            \Storage::disk('public')->delete($event->attachment_path);
        }
        
        $event->delete();

        Notification::make()
            ->title('Event deleted successfully')
            ->success()
            ->send();
    }

    public function viewEvent(int $eventId): void
    {
        $this->viewingEventId = $eventId;
    }

    public function closeViewModal(): void
    {
        $this->viewingEventId = null;
    }

    public function getViewingEventProperty()
    {
        if (!$this->viewingEventId) {
            return null;
        }
        
        return CalendarEvent::with('tasks.assignedTo')->find($this->viewingEventId);
    }

    public function getGoogleCalendarUrlProperty()
    {
        if (!$this->viewingEvent) {
            return '';
        }
        
        $startDate = $this->viewingEvent->event_date->format('Ymd');
        $endDate = $this->viewingEvent->event_date->copy()->addDay()->format('Ymd');
        $title = urlencode($this->viewingEvent->event_title);
        $details = urlencode($this->viewingEvent->notes ?? '');
        $location = urlencode($this->viewingEvent->company_name ?? '');
        
        return "https://www.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$startDate}/{$endDate}&details={$details}&location={$location}";
    }

    public function toggleReminder(int $eventId): void
    {
        $event = CalendarEvent::findOrFail($eventId);
        $event->reminder_enabled = !$event->reminder_enabled;
        $event->save();

        Notification::make()
            ->title($event->reminder_enabled ? 'Reminder enabled' : 'Reminder disabled')
            ->success()
            ->send();
    }

    public function showTaskModal(int $eventId): void
    {
        $this->managingTasksForEventId = $eventId;
        $this->taskDueDate = now()->addDays(7)->format('Y-m-d');
        $this->showTaskModal = true;
    }

    public function closeTaskModal(): void
    {
        $this->showTaskModal = false;
        $this->managingTasksForEventId = null;
        $this->resetTaskForm();
    }

    public function createTask(): void
    {
        $this->validate([
            'taskTitle' => 'required|string|max:255',
            'taskDueDate' => 'required|date',
            'taskAssignedTo' => 'nullable|exists:users,id',
        ]);

        EventTask::create([
            'calendar_event_id' => $this->managingTasksForEventId,
            'assigned_to_id' => $this->taskAssignedTo,
            'task_title' => $this->taskTitle,
            'task_description' => $this->taskDescription,
            'due_date' => $this->taskDueDate,
        ]);

        Notification::make()
            ->title('Task created successfully')
            ->success()
            ->send();

        $this->closeTaskModal();
    }

    public function resetTaskForm(): void
    {
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->taskDueDate = '';
        $this->taskAssignedTo = null;
    }

    public function toggleTaskCompletion(int $taskId): void
    {
        $task = EventTask::findOrFail($taskId);
        $task->completed = !$task->completed;
        $task->save();

        Notification::make()
            ->title($task->completed ? 'Task marked as completed' : 'Task marked as incomplete')
            ->success()
            ->send();
    }

    public function deleteTask(int $taskId): void
    {
        EventTask::findOrFail($taskId)->delete();

        Notification::make()
            ->title('Task deleted successfully')
            ->success()
            ->send();
    }

}
