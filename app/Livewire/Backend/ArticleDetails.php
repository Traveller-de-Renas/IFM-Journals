<?php

namespace App\Livewire\Backend;

use App\Models\User;
use App\Models\Article;
use App\Models\ArticleMovementLog;
use App\Models\Issue;
use App\Models\Volume;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ArticleDetails extends Component
{
    public $record;
    public $reviewers = [];
    public $reviewer_id;
    public $description;

    public $reviewerModal = false;
    public $declineModal = false;
    public $sendModal = false;
    public $assignModal = false;
    public $editorFeedback = false;

    public $users;
    public $role;

    public $volume;
    public $issue;
    public $volumes = [];
    public $issues  = [];

    public $user_id;
    
    public function mount(Request $request){
        if(!Str::isUuid($request->article)){
            abort(404);
        }
        
        $this->record = Article::where('uuid', $request->article)->first();
        if(empty($this->record)){
            abort(404);
        }

        $this->reviewers = User::all();
    }
    
    public function render()
    {
        $volume_all    = $this->record->journal->volumes;
        $this->volumes = $volume_all->pluck('description', 'id')->toArray();
        $this->issues  = Issue::where('volume_id', $this->volume)->get()->pluck('description', 'id')->toArray();
        return view('livewire.backend.article-details');
    }


    public function assignReviewer()
    {
        $this->reviewerModal = true;
    }

    public function assignEditor()
    {
        // $this->reviewers = User::all();
        // $chief = $this->record->journal->chief_editor)->first()->id;

        $this->users = $this->record->journal->editors;
        $this->role = 'editor';
        $this->assignModal = true;
    }

    public function declineArticle()
    {
        $this->dispatch('contentChanged');
        $this->declineModal = true;
    }

    public function sendBack()
    {
        $this->dispatch('contentChanged');
        $this->sendModal = true;
    }

    public function assignRev()
    {
        $this->record->article_users()->sync([$this->reviewer_id => ['role' => 'reviewer']], false);
        
        session()->flash('success', 'Reviewer is Assigned successfully');
        $this->reviewerModal = false;
    }

    public function eFeedback()
    {
        $this->dispatch('contentChanged');
        $this->editorFeedback = true;
    }

    public function attachUser()
    {
        $this->record->article_users()->sync([$this->user_id => ['role' => $this->role]], false);
        
        session()->flash('success', 'Assigned successfully to this Article');
        $this->assignModal = false;
    }

    public function decline()
    {
        $mlog = ArticleMovementLog::create([
            'article_id' => $this->record->id,
            'user_id' => auth()->user()->id,
            'description' => $this->description,
        ]);

        $this->record->status = 'Declined';
        $this->record->save();
        session()->flash('success', 'This Article is Declined');

        $this->reset(['description']);

        $this->declineModal = false;
    }

    public function send_back()
    {
        $mlog = ArticleMovementLog::create([
            'article_id' => $this->record->id,
            'user_id' => auth()->user()->id,
            'description' => $this->description,
        ]);

        $this->record->status = 'From Editorial Board';
        $this->record->save();
        session()->flash('success', 'Done!');

        $this->reset(['description']);

        $this->declineModal = false;
    }

    public function toChiefEditor()
    {
        $mlog = ArticleMovementLog::create([
            'article_id' => $this->record->id,
            'user_id' => auth()->user()->id,
            'description' => $this->description,
        ]);

        $this->record->status = 'From Editor';
        $this->record->save();
        session()->flash('success', 'Successifully Sent to Chief Editor');

        $this->reset(['description']);

        $this->declineModal = false;
    }

    public function updateVolume()
    {
        $this->record->issue_id  = $this->issue;
        $this->record->save();

        session()->flash('success', 'Volume and Issue Updated Successfully!');
    }


    public function changeStatus($status)
    {
        $this->record->status = $status;
        $this->record->update();
        session()->flash('success', 'Article Successifully '.$status.'ed');
    }
}
