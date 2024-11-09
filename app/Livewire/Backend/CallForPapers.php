<?php

namespace App\Livewire\Backend;

use App\Models\CallForPaper;
use Livewire\Component;

class CallForPapers extends Component
{
    public $query;
    public $sortBy  = 'id';
    public $sortAsc = false;

    public $deleteModal;

    public $record;
    public $title;
    public $description;
    public $start_date;
    public $end_date;
    public $image;
    public $category;

    public $form = false;
    public $subjects;

    public function mount()
    {
        //$this->subjects = Subject::all()->pluck('name', 'id')->toArray();
        
    }
    public function render()
    {
        $this->dispatch('contentChanged');

        $call = CallForPaper::when($this->query, function ($query) {
            return $query->where(function ($query) {
                $query->where('title', 'ilike', '%' . $this->query . '%');
            });
        })->orderBy($this->sortBy, $this->sortAsc ? 'ASC' : 'DESC');

        $call = $call->paginate(20);
        return view('livewire.backend.call-for-papers', compact('call'));
    }

    public function store()
    {
        $this->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        CallForPaper::create([
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'user_id' => auth()->user()->id
        ]);

        session()->flash('success', 'You successfully created a new call for papers');

        $this->form = false;
        $this->reset();
    }

    public function edit(CallForPaper $call)
    {
        $this->form = true;
        $this->record = $call;
        $this->title  = $call->title;
        $this->description  = $call->description;
        $this->start_date  = $call->start_date;
        $this->end_date  = $call->end_date;
    }

    public function update(CallForPaper $call)
    {
        $this->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $this->record->update([
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        session()->flash('success', 'You successfully updated this call for papers');

        $this->form = false;
        $this->reset();
    }

    public function confirmDelete(CallForPaper $call)
    {
        $this->record = $call;
        $this->deleteModal = true;
    }

    public function destroy()
    {
        $this->record->delete();
        session()->flash('success', 'You successfully updated this call for papers');
    }
}