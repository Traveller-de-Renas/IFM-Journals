<?php

namespace App\View\Components;

use App\Models\Article;
use App\Models\Journal;
use Illuminate\View\View;
use Illuminate\View\Component;

class AppLayout extends Component
{
    public $journal;
    public $ceditor = [];
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        $this->journal = Journal::where('uuid', session('journal'))->first();

        $submitted = 0;
        $resubmitted = 0;
        $withdecision = 0;
        $onprogress = 0;
        $returned = 0;
        $onreview = 0;
        
        if($this->journal){
            $this->ceditor = $this->journal?->journal_us()->whereHas('roles', function ($query) {
                $query->whereIn('name', ['Chief Editor', 'Supporting Editor', 'Associate Editor']);
            })->get()->pluck('user_id')->toArray();


            //--EDITOR
                //submitted
                $submitted = $this->journal->articles()->whereHas('article_status', function ($query) {
                    $query->where('code', '002');
                })
                ->whereHas('notifications', function ($query) {
                    $juserid = $this->journal?->journal_us()->where('user_id', auth()->user()->id)->first()->id;
                    $query->where('journal_user_id', $juserid)->where('status', 1);
                })
                ->get()->count();

                //resubmitted
                $resubmitted = $this->journal->articles()->whereHas('article_status', function ($query) {
                    $query->whereIn('code', ['006']);
                })
                ->whereHas('notifications', function ($query) {
                    $juserid = $this->journal?->journal_us()->where('user_id', auth()->user()->id)->first()->id;
                    $query->where('journal_user_id', $juserid)->where('status', 1);
                })
                ->get()->count();

            //onreview
                $onreview = $this->journal->articles()->whereHas('article_status', function ($query) {
                    $query->whereIn('code', ['003', '008', '009', '010']);
                })
                ->whereHas('notifications', function ($query) {
                    $juserid = $this->journal?->journal_us()->where('user_id', auth()->user()->id)->first()->id;
                    $query->where('journal_user_id', $juserid)->where('status', 1);
                })
                ->get()->count();


            //--AUTHOR
            //returned
            $returned = $this->journal->articles()->whereHas('article_status', function ($query) {
                $query->whereIn('code', ['004', '019', '020']);
            })
            ->whereHas('notifications', function ($query) {
                $juserid = $this->journal?->journal_us()->where('user_id', auth()->user()->id)->first()->id;
                $query->where('journal_user_id', $juserid)->where('status', 1);
            })
            ->where('user_id', auth()->user()->id)
            ->get()->count();


            //on progress
            $onprogress = $this->journal->articles()->whereHas('article_status', function ($query) {
                $query->whereIn('code', ['002','006','011']);
            })
            ->whereHas('notifications', function ($query) {
                $juserid = $this->journal?->journal_us()->where('user_id', auth()->user()->id)->first()->id;
                $query->where('journal_user_id', $juserid)->where('status', 1);
            })
            ->where('user_id', auth()->user()->id)
            ->get()->count();


            //with decision
            $withdecision = $this->journal->articles()->whereHas('article_status', function ($query) {
                $query->whereIn('code', ['007', '014', '015']);
            })
            ->whereHas('notifications', function ($query) {
                $juserid = $this->journal?->journal_us()->where('user_id', auth()->user()->id)->first()->id;
                $query->where('journal_user_id', $juserid)->where('status', 1);
            })
            ->where('user_id', auth()->user()->id)
            ->get()->count();
        }

        return view('layouts.app', compact('submitted', 'resubmitted', 'withdecision', 'onprogress', 'returned', 'onreview'));
    }
}
