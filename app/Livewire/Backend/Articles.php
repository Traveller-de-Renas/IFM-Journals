<?php

namespace App\Livewire\Backend;

use App\Models\Journal;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class Articles extends Component
{
    public $record;
    
    public function mount(Request $request){

        if(!Str::isUuid($request->journal)){
            abort(404);
        }
        
        $this->record = Journal::where('uuid', $request->journal)->first();
        if(empty($this->record)){
            abort(404);
        }
    }
    
    public function render()
    {
        return view('livewire.backend.articles');
    }
}
