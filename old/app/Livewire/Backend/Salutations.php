<?php

namespace App\Livewire\Backend;

use Livewire\Component;
use App\Models\Salutation;
use Livewire\WithPagination;

class Salutations extends Component
{
    use WithPagination;

    public $query;
    public $sortBy  = 'id';
    public $sortAsc = false; 

    public $Add;
    public $Edit;
    public $Delete;

    public $record;
    public $title;
    public $status;
    public $description;

    public function render()
    {
        $salutations = Salutation::when($this->query, function($query){
            return $query->where(function($query){
                $query->where('title', 'ilike', '%'.$this->query.'%')->orWhere('description', 'ilike', '%'.$this->query.'%');
            });
        })->orderBy($this->sortBy, $this->sortAsc ? 'ASC' : 'DESC');
        
        $salutations = $salutations->paginate(20);

        return view('livewire.backend.salutations', compact('salutations'));
    }

    public function sort($field)
    {
        if ($field == $this->sortBy) {
            $this->sortAsc = !$this->sortAsc;
        }
        $this->sortBy = $field;
    }

    public function confirmAdd()
    {
        $this->Add = true;
    }

    public function confirmEdit(Salutation $data)
    {
        $this->record = $data->id;
        $this->title = $data->title;
        $this->description = $data->description;
        $this->status = $data->status;

        $this->Edit = true;
    }

    public function confirmDelete(Salutation $data)
    {
        $this->record = $data->id;
        $this->Delete = true;
    }

    public function rules()
    {
        return [
            'title'       => 'required|string',
            'description' => 'string',
        ];
    }

    public function store()
    {
        $this->validate();
        $data = new Salutation;
        $data->create([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        
        session()->flash('success', 'Saved Successifully');
        $this->Add = false;
    }

    public function update(Salutation $data)
    {
        $this->validate();
        $data->update([
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
        ]);
        
        session()->flash('success', 'Updated Successifully');
        $this->Edit = false;
    }

    public function delete(Salutation $menu)
    {
        if($menu->delete()){
            session()->flash('success', 'Deleted Successifully');
            $this->Delete = false;
        }
    }
}
