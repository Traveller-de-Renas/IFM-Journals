<div class="bg-white shadow-md p-4 rounded">
    
    <div class="w-full grid grid-cols-3 gap-4" >
        <div class="">
            <p class="font-bold text-xl">{{ __('LIST OF ARTICLES') }}/{{ strtoupper(str_replace('_', ' ', $this->status)) }}
            </p>
        </div>
        <div class="">

        </div>
        <div class="flex gap-2 justify-end">
            <x-input wire:model.live.debounce.500ms="query" placeholder="search..." type="search" />
        </div>
    </div>

    

    @if (session('response'))
        @php
            $bgClass = match (session('response.status')) {
                'success' => 'bg-green-300',
                'danger'  => 'bg-red-300',
                'warning' => 'bg-yellow-300',
                'info'    => 'bg-blue-300',
                default   => 'bg-gray-200',
            };
        @endphp
        <div class="p-4 text-sm mb-4 mt-2 shadow {{ $bgClass }} w-full text-center">
            {{ session('response.message') }}
        </div>
    @endif

    <table class="min-w-full text-left text-sm font-light">
        <thead class="border-b font-medium grey:border-neutral-500">
            <tr>
                <th scope="col" class="px-6 py-4 w-2">#</th>
                <th scope="col" class="px-6 py-4 ">
                    <button wire:click="sort('title')" >Title</button>
                    <x-sort-icon class="float-right" sortField="title" :sort-by="$sortBy" :sort-asc="$sortAsc" />
                </th>
                <th scope="col" class="px-6 py-4 w-2">Paper Id</th>
                <th scope="col" class="px-6 py-4 w-2">Submitted On</th>
                <th scope="col" class="px-6 py-4 w-2">Decision/Status</th>
                <th scope="col" class="py-4 w-2" ></th>
            </tr>
        </thead>
        <tbody>
            @php
                $sn = 1;
            @endphp
            @foreach ($articles as $article)

            <tr class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 grey:border-neutral-500 grey:hover:bg-neutral-600 {{ $article->notifications()->where('status', 1)->where('journal_user_id', $journal->journal_us()->where('user_id', auth()->user()?->id)->first()?->id)->count() > 0 ? 'text-red-600 font-semibold' : '' }}">
                <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $sn }}</td>
                <td class="whitespace-normal px-6 py-3 break-words">
                    {{ $article->title }}
                    @php
                        $ass_editor = $article->article_journal_users()->whereHas('roles', function ($query) {
                            $query->where('name', 'Associate Editor');
                        })->first();

                        $m_editors = $article->journal->journal_us()->whereHas('roles', function ($query) {
                            $query->where('name', 'Chief Editor');
                        })->pluck('user_id')->toArray();
                    @endphp

                    {{ $ass_editor }}

                    {{-- @if(!empty($ass_editor) && in_array(auth()->user()->id, $m_editor))
                        <p class="text-xs">
                            {{ $ass_editor->user->first_name }}
                            {{ $ass_editor->user->middle_name }}
                            {{ $ass_editor->user->last_name }}

                            ({{ $ass_editor->user->email }})
                        </p>
                    @endif --}}
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                    {{ $article->paper_id }}
                </td>
                <td class="whitespace-nowrap px-6 py-4">{{ $article->created_at }}</td>
                <td class="whitespace-nowrap px-6 py-4">
                    <div class="px-2 text-[12px] text-center rounded" style="{{ $article->article_status->color }}">
                        {{ $article->article_status->name }}
                    </div>
                </td>
                <td class="">
                    
                    <button id="dropdown{{ $article->id }}" data-dropdown-toggle="dropdownDots{{ $article->id }}" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-900" type="button">
                        <svg class="h-6 w-6 text-gray-500"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <circle cx="12" cy="12" r="1" />  <circle cx="19" cy="12" r="1" />  <circle cx="5" cy="12" r="1" /></svg>
                    </button>
                    
                    <div id="dropdownDots{{ $article->id }}" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow " >
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdown{{ $article->id }}">

                            @if ($article->article_status->code == '001' || $article->article_status->code == '005')
                            <li>
                                <a href="{{ route('journals.submission', ['journal' => session('journal'), 'article' => $article->uuid]) }}" class="block px-4 py-2 hover:bg-gray-100" wire:loading.attr="disabled">Continue Submission</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100" wire:click="confirmDelete({{ $article->id }})" wire:loading.attr="disabled">Delete</a>
                            </li>
                            @endif

                            @if (($article->article_status->code == '002' || $article->article_status->code == '006') && $article->user_id == auth()->user()->id)
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100" wire:click="confirmXS({{ $article->id }})" wire:loading.attr="disabled">Cancel Submission</a>
                            </li>
                            @endif

                            @if (($article->article_status->code == '004' || $article->article_status->code == '019' || $article->article_status->code == '020') && $article->user_id == auth()->user()->id)
                            <li>
                                <a href="{{ route('journals.submission', ['journal' => session('journal'), 'article' => $article->uuid]) }}" class="block px-4 py-2 hover:bg-gray-100" wire:loading.attr="disabled">Resubmission</a>
                            </li>
                            @endif

                            @php
                                $ceditor = $journal->journal_us()->whereHas('roles', function ($query) {
                                    $query->whereIn('name', ['Chief Editor', 'Supporting Editor']);
                                })->get()->pluck('user_id')->toArray();
                            @endphp

                            @if (($article->article_status->code == '002') && in_array(auth()->user()->id, $ceditor))
                            <li>
                                <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="openDrawer({{ $article->id }})" wire:loading.attr="disabled">Screening Stage</button>
                            </li>
                            @endif

                            @php
                                $caeditors = $journal->journal_us()->whereHas('roles', function ($query) {
                                    $query->where('name', 'Chief Editor');
                                })->get()->pluck('user_id');

                                $aeditors = $journal->journal_us()->whereHas('roles', function ($query) {
                                    $query->where('name', 'Associate Editor');
                                })->get()->pluck('user_id');
                            @endphp

                            @if (($article->article_status->code == '003' || $article->article_status->code == '008' || $article->article_status->code == '009') && $caeditors->contains(auth()->user()->id))
                                <li>
                                    <button class="block px-4 py-2 hover:bg-gray-100" wire:click="openDrawerA({{ $article->id }})" wire:loading.attr="disabled">Assign Associate Editor</button>
                                </li>
                            @endif


                            @if ($article->article_status->code == '003' && $caeditors->contains(auth()->user()->id))
                                <li>
                                    <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="openDrawerH({{ $article->id }})" wire:loading.attr="disabled">Reject Manuscript</button>
                                </li>
                            @endif

                            @if ($article->article_status->code == '008' && $aeditors->contains(auth()->user()->id))
                                <li>
                                    <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="openDrawerC({{ $article->id }})" wire:loading.attr="disabled">Return to Managing Editor</button>
                                </li>
                            @endif

                            @if (($article->article_status->code == '003' || $article->article_status->code == '006' || $article->article_status->code == '008' || $article->article_status->code == '010'))
                                @if (($aeditors->contains(auth()->user()->id) || $caeditors->contains(auth()->user()->id)))
                                    <li>
                                        <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="openDrawerB({{ $article->id }})" wire:loading.attr="disabled">Send to Reviewer</button>
                                    </li>
                                @endif
                            @endif

                            
                            @if ($article->article_status->code == '006' && ($aeditors->contains(auth()->user()->id) || $caeditors->contains(auth()->user()->id)))
                                <li>
                                    <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="acceptResubmission({{ $article->id }})" wire:loading.attr="disabled">Accept for Production</button>
                                </li>
                            @endif



                            @if($article->article_status->code == '009' && ($aeditors->contains(auth()->user()->id) || $caeditors->contains(auth()->user()->id)))
                                <li>
                                    <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="openDrawerE({{ $article->id }})" wire:loading.attr="disabled">Return to Author</button>
                                </li>
                            @endif

                            @if($article->article_status->code == '010' && $aeditors->contains(auth()->user()->id))
                                <li class="ml-42">
                                    <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="openDrawerI({{ $article->id }})" wire:loading.attr="disabled">Return to Managing Editor</button>
                                </li>
                            @endif
                            


                            @if(($article->article_status->code == '018') && $aeditors->contains(auth()->user()->id))
                            <li>
                                <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="openDrawerG({{ $article->id }})" wire:loading.attr="disabled">Pre-Publication Phase</button>
                            </li>
                            @endif


                            @if($article->article_status->code == '013' && $aeditors->contains(auth()->user()->id))
                            <li>
                                <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="confirmPublish({{ $article->id }})" wire:loading.attr="disabled">Publish Online</button>
                            </li>
                            @endif


                            <li>
                                <button class="block px-4 py-2 hover:bg-gray-100 w-full text-start" wire:click="openDrawerF({{ $article->id }})" wire:loading.attr="disabled">Manuscript Comments</button>
                            </li>

                            <li>
                                <a href="{{ route('journals.article', $article->uuid) }}" class="block px-4 py-2 hover:bg-gray-100 text-start" wire:loading.attr="disabled">View Manuscript</a>
                            </li>

                        </ul>
                    </div>

                </td>
            </tr>
            
            @php
                $sn++;
            @endphp
            @endforeach
        
        </tbody>
    </table>

    <div class="mt-4 w-full">
        {{ $articles->links() }}
    </div>


    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12 {{ $isOpen ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Screening Stage
            </h5>
    
            <button wire:click="closeDrawer" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
                <br>
            </p>


            @php
                $to_author = \App\Models\EditorChecklist::whereHas('editorialProcess', function ($query){
                    $query->where('code', '001');
                })->get();
            @endphp

            @foreach ($to_author as $key => $checki)
                <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck({{ $checki->id }})">
                    <x-input type="checkbox" value="rounded" class="" wire:model.live="check.{{ $checki->id }}" />
                    <span class="ms-3 text-sm font-medium text-gray-900 w-full">
                        {{ $checki->description }}
                    </span>
                </label>
            @endforeach


            
            <div class="mt-2 mb-6 flex flex-col justify-between gap-2">
                

                @if($record)
                    @if (empty(array_diff($to_author->pluck('id')->toArray(), $record->editorChecklists->pluck('id')->toArray())))

                        <div class="bg-green-500 text-white p-2 px-4 text-xs rounded">
                            Status : Passed Screening Stage
                        </div>

                    @else

                        <div class="bg-red-500 text-white p-2 px-4 text-xs rounded">
                            Status : Return this Manuscript to Author
                        </div>
                        
                        <div class="mt-4">
                            <x-label for="description" value="Editor Comments" class="mb-2 block font-medium text-sm text-gray-700" />
                            <x-textarea type="text" id="description" class="w-full" wire:model="description" rows="6" />
                        </div>
                        <div class="mt-2">
                            <x-input-error for="description" />
                        </div>

                    @endif
                @endif
            </div>
            
            @if($record)
                @if(empty(array_diff($to_author->pluck('id')->toArray(), $record->editorChecklists->pluck('id')->toArray())))
                    <x-button class="float-right mt-4" wire:click="guidelineCompliance('003')" wire:loading.attr="disabled" >
                        {{ 'Submit to Managing Editor' }}
                    </x-button>
                @else
                    <x-button class="float-right mt-4" wire:click="guidelineCompliance('004')" wire:loading.attr="disabled" >
                        {{ 'Return back to Author' }}
                    </x-button>
                @endif
            @endif

                
        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawer" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpen ? 'block' : 'hidden' }}"></div>


    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12 {{ $isOpenA ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Assign Associate Editor
            </h5>
    
            <button wire:click="closeDrawerA" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
            </p>

            <p class="mb-2 mt-2 text-sm text-gray-500">
                Manuscript adheres to scientific quality (for further peer review processes).
            </p>



            @php
                $to_associate = \App\Models\EditorChecklist::whereHas('editorialProcess', function ($query){
                    $query->where('code', '002');
                })->get();
            @endphp

            @foreach ($to_associate as $key => $checki)
                <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck({{ $checki->id }})">
                    <x-input type="checkbox" value="rounded" class="" wire:model="check.{{ $checki->id }}" />
                    <span class="ms-3 text-sm font-medium text-gray-900 w-full">
                        {{ $checki->description }}
                    </span>
                </label>
            @endforeach
            

 
            

            @if($record)
                @if (empty(array_diff($to_associate->pluck('id')->toArray(), $record->editorChecklists->pluck('id')->toArray())))
                
                    <p class="mb-6 mt-6 text-sm text-gray-500">
                        Search Associate Editor from Editorial Team of this journal.
                    </p>

                    @if($record)
                        @php
                            $editor = $record->article_journal_users()->whereHas('roles', function ($query) {
                                $query->where('name', 'Associate Editor');
                            })->first();
                        @endphp
                        
                        @if(!empty($editor))
                            <p class="mt-2 text-xs text-gray-500">Currently Assigned Associate Editor is </p>
                            <div class="mb-2 font-bold">
                                {{ $editor->user->first_name }}
                                {{ $editor->user->middle_name }}
                                {{ $editor->user->last_name }}

                                ({{ $editor->user->email }})
                            </div>

                            <x-button class="bg-red-500 hover:bg-red-700" wire:click="removeEditor({{ $editor->id }})">Remove Associate Editor</x-button>
                        @else
                            <div class="mb-2">
                                No Associate Editor Assigned to this Article
                            </div>

                            <x-input class="w-full" wire:model="username" wire:keyup="searchUser($event.target.value, 'Associate Editor')" placeholder="Search Associate Editor" />

                            <div>
                                @if(count($users) == 0 && $search_user != '')
                                    <p class="py-2 w-full text-center text-sm text-red-600 bg-gray-200 rounded">No Associate Editor Found</p>
                                @else
                                    @foreach ($users as $user)
                                        <div class="py-2 flex border-b">
                                            <div class="w-full">{{ $user->user->first_name }} {{ $user->user->middle_name }} {{ $user->user->last_name }}</div>
                                            <x-button wire:click="assignEditor({{ $user->id }})">Assign</x-button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        @endif
                    @endif

                    
                @endif
            @endif

        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawerA" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpenA ? 'block' : 'hidden' }}"></div>


    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12 {{ $isOpenB ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Send Manuscript to Reviwer
            </h5>
    
            <button wire:click="closeDrawerB" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
            </p>


            {{-- <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck('001', 'checklist2')">
                <x-input type="checkbox" value="rounded" class="" wire:model="check.001" />
                <span class="ms-3 text-sm font-medium text-gray-900 w-full">Coherence and Structure: manuscript presents a clear, logical, and well-structured argument throughout</span>
            </label>

            <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck('002', 'checklist2')">
                <x-input type="checkbox" value="rounded" class="" wire:model="check.002" />
                <span class="ms-3 text-sm font-medium text-gray-900 w-full">Methodology and Findings: clear (well defined), appropriate, and robust methodology and findings are credible</span>
            </label>

            <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck('003', 'checklist2')">
                <x-input type="checkbox" value="rounded" class="" wire:model="check.003" />
                <span class="ms-3 text-sm font-medium text-gray-900 w-full">Originality: manuscript’s topic is timely, significant, and pertinent to current research trends or practical issues within the field</span>
            </label>

            <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck('004', 'checklist2')">
                <x-input type="checkbox" value="rounded" class="" wire:model="check.004" />
                <span class="ms-3 text-sm font-medium text-gray-900 w-full">Literature review: comprehensive and up-to-date</span>
            </label>

            <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck('005', 'checklist2')">
                <x-input type="checkbox" value="rounded" class="" wire:model="check.005" />
                <span class="ms-3 text-sm font-medium text-gray-900 w-full">In case of revision: necessary corrections suggested by reviewers have been implemented.</span>
            </label> --}}

            @php
                $to_reviewer = \App\Models\EditorChecklist::whereHas('editorialProcess', function ($query){
                    $query->where('code', '002');
                })->get();
            @endphp

            @foreach ($to_reviewer as $key => $checki)
                <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck('001', 'checklist2')">
                    <x-input type="checkbox" value="rounded" class="" wire:model="check.{{ $checki->id }}" />
                    <span class="ms-3 text-sm font-medium text-gray-900 w-full">
                        {{ $checki->description }}
                    </span>
                </label>
            @endforeach


            {{-- <div class="flex items-center ps-2 border border-gray-200 rounded p-2 mb-1">
                <label class="inline-flex items-center cursor-pointer w-full" wire:click="selectCheck('scope')">
                    <span class="ms-3 text-sm font-medium text-gray-900 w-full">Paper is with Scope</span>
                    <div>
                        <input type="checkbox" class="sr-only peer" wire:model="scope" {{ $rev_count > 0 ? 'disabled' : '' }} >
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 "></div>
                    </div>
                </label>
            </div>

            <div class="flex items-center ps-2 border border-gray-200 rounded p-2 mb-1">
                <label class="inline-flex items-center cursor-pointer w-full" wire:click="selectCheck('tech_complete')">
                    <span class="ms-3 text-sm font-medium text-gray-900  w-full">Is the Paper Technical Completeness</span>
                    <div>
                        <input type="checkbox" class="sr-only peer" wire:model="tech_complete">
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300  peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </div>
                </label>
            </div>

            <div class="flex items-center ps-2 border border-gray-200 rounded p-2 mb-1">
                <label class="inline-flex items-center cursor-pointer w-full" wire:click="selectCheck('noverity')">
                    <span class="ms-3 text-sm font-medium text-gray-900 w-full">Does the Paper has Noverity</span>
                    <div>
                        <input type="checkbox" class="sr-only peer" wire:model="noverity">
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 "></div>
                    </div>
                </label>
            </div>


            <div class="flex items-center ps-2 border border-gray-200 rounded p-2 mb-1">
                <label class="inline-flex items-center cursor-pointer w-full" wire:click="selectCheck('prior_publication')">
                    <span class="ms-3 text-sm font-medium text-gray-900 w-full">Not Prior Published</span>
                    <div>
                        <input type="checkbox" class="sr-only peer" wire:model="prior_publication">
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 "></div>
                    </div>
                </label>
            </div>


            <div class="flex items-center ps-2 border border-gray-200 rounded p-2 mb-6">
                <label class="inline-flex items-center cursor-pointer w-full" wire:click="selectCheck('methodology')">
                    <span class="ms-3 text-sm font-medium text-gray-900  w-full">Methodological Rigorous</span>
                    <div>
                        <input type="checkbox" class="sr-only peer" wire:model="methodology">
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </div>
                </label>
            </div> --}}
            

            {{-- @if($scope == true && $prior_publication == true && $methodology == true && $noverity == true && $tech_complete == true) --}}

            @if($record)
                @if (empty(array_diff($to_associate->pluck('id')->toArray(), $record->editorChecklists->pluck('id')->toArray())))
                    <p class="mb-2 mt-2 text-sm text-gray-500 ">
                        Search Reviwer from Journal Reviewers of this journal.
                    </p>

                    <x-input class="w-full" wire:model="username" wire:keyup="searchUser($event.target.value, 'Reviewer')" placeholder="Search Reviewers" />

                    <div>
                        @if(count($users) == 0 && $search_user != '')
                            <p class="py-2 w-full text-center text-sm text-red-600 bg-gray-200 rounded">No Reviewer Found</p>
                        @else
                            @foreach ($users as $user)
                                <div class="py-2 flex border-b hover:bg-gray-100 cursor-pointer" wire:click="selectUser({{ $user->id }})">
                                    <div class="w-full px-2">{{ $user->user->first_name }} {{ $user->user->middle_name }} {{ $user->user->last_name }}</div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    @if(count($users_x) > 0)
                        <div class="mt-4">
                            @foreach ($users_x as $key => $user_s)
                                <div class="flex gap-2 border-b pb-2">
                                    <div class="w-full">
                                        <x-label for="end_date" value="Reviewer" class="mb-1 block font-medium text-xs text-gray-700" />
                                        {{ $user_s->user->first_name }} {{ $user_s->user->middle_name }} {{ $user_s->user->last_name }}
                                    </div>
                                    <div class="">
                                        <x-label for="end_dates" value="Review End Date" class="mb-1 block font-medium text-xs text-gray-700" />
                                        <x-input type="date" class="w-full" wire:model="end_dates.{{ $key }}" />
                                        <x-input-error for="end_dates" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif


                        <div class="text-right">
                            <x-button class="mt-4 mb-4 bg-green-500 hover:bg-green-700" wire:click="sendToReviewer()" wire:loading.attr="disabled">Send to Reviewer(s)</x-button>
                        </div>
                    @endif

                    <hr>

                    @if($record)
                        @php
                            $reviewers = $record->article_journal_users()->whereHas('roles', function ($query) {
                                $query->where('name', 'Reviewer');
                            })->get();
                        @endphp
                        
                        @if(!empty($reviewers))
                            <p class="mt-2 text-xs text-gray-500 ">Currently Assigned Reviewers</p>
                            @foreach($reviewers as $key => $reviewer)
                                <div class="mb-2 font-bold flex gap-2 w-full">
                                    <div>
                                        {{ ++$key }}
                                    </div>
                                    <div class="w-full">
                                        {{ $reviewer->user->first_name }}
                                        {{ $reviewer->user->middle_name }}
                                        {{ $reviewer->user->last_name }}

                                        {{-- {{ $reviewer->id }} --}}

                                        @php
                                            $rstatus = $reviewer->article_journal_users()->where('article_id', $record->id)->first()->pivot->review_status;
                                        @endphp
                                    </div>
                                    <div class="text-right {{ $rstatus == 'completed' ? 'text-green-700':''; }}">
                                        {{ $rstatus }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="mb-2 mt-2 p-2 text-center bg-gray-200 rounded shadow-sm">
                                No Reviewer any Reviewer was Requested to Review this Manuscript
                            </div>
                        @endif
                    @endif
            @endif

        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawerB" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpenB ? 'block' : 'hidden' }}"></div>


    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12  {{ $isOpenC ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500 ">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Send Recommendations to Managing Editor
            </h5>
    
            <button wire:click="closeDrawerC" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center  ">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
            </p>

            <div class="mb-2 w-full">

                <div class="mt-4" wire:ignore>
                    <x-label for="description" value="Description" class="mb-2 block font-medium text-sm text-gray-700" />
                    <x-textarea type="text" id="description" class="w-full" wire:model="description" rows="6" />
                    <x-input-error for="description" />
                </div>

            </div>

            <div class="text-right">
                <x-button class="mt-4 mb-4" wire:click="recommendations()" wire:loading.attr="disabled" >Send to Managing Editor</x-button>
            </div>
            

            <div class="border-b mb-6"></div>

            <div>
                @if(!empty($record?->article_comments))
                    @foreach($record?->article_comments()->orderBy('id', 'DESC')->get() as $comment) 
                        <div class="bg-gray-200 shadow-sm p-2 rounded mb-2">
                            {!! $comment->description !!}
                            <div class="text-gray-600 text-xs">
                                {{ $comment->user->first_name }} {{ $comment->user->last_name }},  {{ $comment->created_at }}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawerC" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpenC ? 'block' : 'hidden' }}"></div>


    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12  {{ $isOpenD ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500 ">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Manuscript Publication Check
            </h5>
    
            <button wire:click="closeDrawerD" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center  ">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
            </p>

            <div class="mb-2 w-full">

                <div class="flex items-center ps-4 border border-gray-200 rounded flex-1 mb-2">
                    <input id="editorial" type="checkbox" wire:model="editorial" value="" name="bordered-checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="editorial" class="w-full py-4 ms-2 text-sm font-medium text-gray-900 ">Editorial Completed</label>
                </div>
                <x-input-error for="editorial" />

                <div class="flex items-center ps-4 border border-gray-200 rounded flex-1 mb-2">
                    <input id="type_setting" type="checkbox" wire:model="type_setting" value="" name="bordered-checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500  focus:ring-2">
                    <label for="type_setting" class="w-full py-4 ms-2 text-sm font-medium text-gray-900 ">Type Setting Completed</label>
                </div>
                <x-input-error for="type_setting" />

                

            </div>

            <div class="text-right">
                <x-button class="mt-4 mb-4" wire:click="publicationCheck()" wire:loading.attr="disabled" >Submit</x-button>
            </div>
            
        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawerD" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpenD ? 'block' : 'hidden' }}"></div>


    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12  {{ $isOpenE ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500 ">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Return Manuscript to Author
            </h5>
    
            <button wire:click="closeDrawerE" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center  ">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
            </p>


            <p class="text-sm text-gray-500">Select Review Comments you want to send to Author by checking on the checkboxes</p>

            <div class="mb-2 w-full">
                @if (!empty($record?->article_journal_users))
                @php
                    $journal_users = $record?->article_journal_users()->whereHas('roles', function ($query) {
                        $query->where('name', 'Reviewer');
                    })->get();
                @endphp
                @if ($journal_users->count() > 0)
                    @foreach ($journal_users as $key => $journal_user)
                    @php
                        $r_status = $journal_user->pivot->review_status;
                    @endphp
                        <div class="flex justify-between text-sm items-center border-b p-2 ">
                            <div class="w-8/12 flex gap-2">
                                @if ($r_status == 'completed')
                                    <x-input type="checkbox" value="{{ $journal_user->id }}" class="" wire:model="send.{{ $journal_user->id }}" id="send.{{ $journal_user->id }}" />
                                @else
                                    <x-input type="checkbox" value="{{ $journal_user->id }}" class="bg-gray-500" wire:model="send.{{ $journal_user->id }}" id="send.{{ $journal_user->id }}" disabled/>
                                @endif
                                
                                <x-label for="send.{{ $journal_user->id }}">
                                    {{ $journal_user->user->first_name }} {{ $journal_user->user->middle_name }} {{ $journal_user->user->last_name }}
                                </x-label>
                            </div>
                            
                            <div class="w-2/12 text-right">
                                {{ $journal_user->pivot->review_end_date }}
                            </div>

                            <div class="w-2/12 text-right pl-4">
                                @if($journal_user->pivot->review_status == 'completed')
                                    <button class="text-xs bg-green-500 hover:bg-green-700 rounded-md shadow-sm cursor-pointer p-1 px-2 text-white" wire:click="reviewFeedback({{ $journal_user->user->id }})" wire:loading.attr="disabled" title="View Comments">
                                        <svg class="h-4 w-4 text-white"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />  <circle cx="12" cy="12" r="3" /></svg>
                                    </button>
                                @else
                                    <button class="-text-xs bg-gray-400 hover:bg-gray-200 rounded-md shadow-sm cursor-pointer p-1 px-2 text-black" wire:loading.attr="disabled" title="No Comments to View">
                                        <svg class="h-4 w-4 text-white"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round">  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />  <line x1="1" y1="1" x2="23" y2="23" /></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
                @endif
                
            </div>

            <div class="mt-8">
                <div class="mt-4">
                    <x-label for="review_status" value="General Review Status" class="mb-2 block font-medium text-sm text-gray-700" />
                    <x-select id="review_status" class="w-full" :options="['018'=>'Accepted for Production', '019'=>'Accepted with minor Revisions', '020'=>'Accepted with major Revisions', '015'=>'Not Acceptable']" wire:model.live="review_status" wire:change="getCheckList($event.target.value)" />
                    <x-input-error for="review_status" />
                </div>

                @php
                    $to_author = \App\Models\EditorChecklist::whereHas('editorialProcess', function ($query){
                        $query->where('code', $this->eprocess);
                    })->get();
                @endphp

                @foreach ($to_author as $key => $checki)
                    <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck({{ $checki->id }})">
                        <x-input type="checkbox" value="rounded" class="" wire:model.live="check.{{ $checki->id }}" />
                        <span class="ms-3 text-sm font-medium text-gray-900 w-full">
                            {{ $checki->description }}
                        </span>
                    </label>
                @endforeach

                <div class="{{ $review_status == "018" ? 'hidden' : 'block'}}">
                    <div class="mt-4" wire:ignore >
                        <x-label for="editor_comments" value="Editor Comments" class="mb-2 block font-medium text-sm text-gray-700" />
                        <x-textarea type="text" id="editor_comments" class="w-full" wire:model="editor_comments" placeholder="Enter Editor Comments" rows="7" />
                        <x-input-error for="editor_comments" />
                    </div>
                </div>
            </div>

            <div class="text-right">
                @if ($review_status == "018")
                    @if($record)
                        @if (empty(array_diff($to_associate->pluck('id')->toArray(), $record->editorChecklists->pluck('id')->toArray())))
                            <x-button class="mt-4 mb-4" wire:click="acceptedForProduction()" wire:loading.attr="disabled" >
                                Submit for Production
                            </x-button>
                        @endif
                    @endif
                @else
                    <x-button class="mt-4 mb-4" wire:click="generalReviewStatus()" wire:loading.attr="disabled" >
                        Return Manuscript
                    </x-button>
                @endif
                
            </div>
        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawerE" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpenE ? 'block' : 'hidden' }}"></div>





    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12  {{ $isOpenI ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500 ">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Return Manuscript to Managing Editor
            </h5>
    
            <button wire:click="closeDrawerI" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center  ">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
            </p>
            

            <div class="mt-8">
                <div class="mt-4">
                    <x-label for="review_status" value="General Review Status" class="mb-2 block font-medium text-sm text-gray-700" />
                    <x-select id="review_status" class="w-full" :options="['018'=>'Accepted for Production', '019'=>'Accepted with minor Revisions', '020'=>'Accepted with major Revisions', '015'=>'Not Acceptable']" wire:model.live="review_status" wire:change="getCheckList($event.target.value)" />
                    <x-input-error for="review_status" />
                </div>


                @php
                    $to_associate = \App\Models\EditorChecklist::whereHas('editorialProcess', function ($query){
                        $query->where('code', $this->eprocess);
                    })->get();
                @endphp

                @foreach ($to_associate as $key => $checki)
                    <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck({{ $checki->id }})">
                        <x-input type="checkbox" value="rounded" class="" wire:model.live="check.{{ $checki->id }}" />
                        <span class="ms-3 text-sm font-medium text-gray-900 w-full">
                            {{ $checki->description }}
                        </span>
                    </label>
                @endforeach

                <div class="{{ $review_status == "018" ? 'hidden' : 'block'}}">
                    <div class="mt-4" wire:ignore >
                        <x-label for="editor_comments" value="Editor Comments" class="mb-2 block font-medium text-sm text-gray-700" />
                        <x-textarea type="text" id="editor_comments" class="w-full" wire:model="editor_comments" placeholder="Enter Editor Comments" rows="7" />
                        <x-input-error for="editor_comments" />
                    </div>
                </div>
            </div>


            <div class="text-right">
                @if ($review_status == "018")
                    @if($record)
                        @if (empty(array_diff($to_associate->pluck('id')->toArray(), $record->editorChecklists->pluck('id')->toArray())))
                            <x-button class="mt-4 mb-4" wire:click="acceptedForProduction()" wire:loading.attr="disabled" >
                                Submit for Production
                            </x-button>
                        @endif
                    @endif
                @else
                    <x-button class="mt-4 mb-4" wire:click="returnManuscript('managing_editor')" wire:loading.attr="disabled" >
                        Return Manuscript
                    </x-button>
                @endif
                
            </div>
        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawerI" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpenI ? 'block' : 'hidden' }}"></div>





    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12  {{ $isOpenF ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500 ">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Article Comments From Editorial Team
            </h5>
    
            <button wire:click="closeDrawerF" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center  ">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
            </p>

            <div>
                @if($record?->article_comments->count() > 0)
                    @foreach($record?->article_comments()->orderBy('id', 'DESC')->get() as $comment) 
                        <div class="bg-gray-200 shadow-sm p-2 rounded mb-2">
                            {!! $comment->description !!}
                            <div class="text-gray-600 text-xs">
                                {{ $comment->user->first_name }} {{ $comment->user->last_name }},  {{ $comment->created_at }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bg-red-500 shadow-sm p-2 rounded mb-2 text-sm text-center text-white">No Comments Found</div>
                @endif
            </div>
        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawerF" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpenF ? 'block' : 'hidden' }}"></div>


    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12  {{ $isOpenH ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500 ">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Send Back Manuscript to Author
            </h5>
    
            <button wire:click="closeDrawerH" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center  ">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
            </p>


            @php
                $checklist = \App\Models\EditorChecklist::whereHas('editorialProcess', function ($query){
                    $query->where('code', '003');
                })->get();
            @endphp

            @foreach ($checklist as $key => $check)
                <label class="inline-flex items-center cursor-pointer w-full border-b py-2" wire:click="selectCheck('001', 'checklist2')">
                    <x-input type="checkbox" value="rounded" class="" wire:model="check.001" />
                    <span class="ms-3 text-sm font-medium text-gray-900 w-full">
                        {{ $check->description }}
                    </span>
                </label>
            @endforeach

            
           
            <div class="mt-4" wire:ignore >
                <x-label for="editor_comments" value="Editor Comments" class="mb-2 block font-medium text-sm text-gray-700" /><span></span>
                <x-textarea type="text" id="editor_comments" class="w-full" wire:model="editor_comments" placeholder="Enter Editor Comments" rows="7" />
            </div>
            <div class="mt-2">
                <x-input-error for="editor_comments" />
            </div>
            

            <div class="text-right">
                <x-button class="mt-4 mb-4" wire:click="rejectManuscript()" wire:loading.attr="disabled" >
                    Reject Manuscript
                </x-button>
            </div>
        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawerH" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpenH ? 'block' : 'hidden' }}"></div>



    <div>
        <div 
            class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform bg-white w-5/12  {{ $isOpenG ? 'translate-x-0' : 'translate-x-full' }}" 
            style="transition: transform 0.3s ease-in-out;"
        >
            <h5 class="inline-flex items-center mb-4 text-base font-semibold text-gray-500 ">
                <svg class="w-4 h-4 me-2.5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
                Pre-Publication Phase
            </h5>
    
            <button wire:click="closeDrawerG" 
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center  ">
                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <hr>
    
            <p class="mb-6 mt-2 font-bold">
                Title : {{ $record?->title }}
            </p>

            <p class="text-sm text-gray-500">To submit this manuscript for publication please confirm the following checklist </p>

            <div class="flex items-center ps-2 border border-gray-200 rounded  p-2 mb-1">
                <label class="inline-flex items-center cursor-pointer w-full" wire:click="selectCheck('formatting')">
                    <span class="ms-3 text-sm font-medium text-gray-900  w-full">Formatting</span>
                    <div>
                        <input type="checkbox" class="sr-only peer" id="formatting" wire:model="formatting" {{ $rev_count > 0 ? 'disabled' : '' }} >
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300   peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all  peer-checked:bg-blue-600 "></div>
                    </div>
                </label>
            </div>
            <x-input-error for="formatting" />

            <div class="flex items-center ps-2 border border-gray-200 rounded  p-2 mb-1">
                <label class="inline-flex items-center cursor-pointer w-full" wire:click="selectCheck('copyediting')">
                    <span class="ms-3 text-sm font-medium text-gray-900  w-full">Copyediting</span>
                    <div>
                        <input type="checkbox" class="sr-only peer" id="copyediting" wire:model="copyediting">
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300   peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all  peer-checked:bg-blue-600 "></div>
                    </div>
                </label>
            </div>
            <x-input-error for="copyediting" />

            <div class="flex items-center ps-2 border border-gray-200 rounded  p-2 mb-1">
                <label class="inline-flex items-center cursor-pointer w-full" wire:click="selectCheck('typesetting')">
                    <span class="ms-3 text-sm font-medium text-gray-900  w-full">Typesetting</span>
                    <div>
                        <input type="checkbox" class="sr-only peer" id="typesetting" wire:model="typesetting">
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300   peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all  peer-checked:bg-blue-600 "></div>
                    </div>
                </label>
            </div>
            <x-input-error for="typesetting" />

            <div class="flex items-center ps-2 border border-gray-200 rounded  p-2 mb-1">
                <label class="inline-flex items-center cursor-pointer w-full" wire:click="selectCheck('proofreading')">
                    <span class="ms-3 text-sm font-medium text-gray-900  w-full">Proofreading</span>
                    <div>
                        <input type="checkbox" class="sr-only peer" id="proofreading" wire:model="proofreading">
                        <div class="relative w-11 h-6 bg-gray-200 rounded-full peer peer-focus:ring-4 peer-focus:ring-blue-300   peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all  peer-checked:bg-blue-600 "></div>
                    </div>
                </label>
            </div>
            <x-input-error for="proofreading" />

            @if($proofreading == true && $typesetting == true && $copyediting == true && $formatting = true)

            <div class="mt-4">
                <x-label for="manuscript_file" value="Select PDF file for Publication" class="mb-2 block font-medium text-sm text-gray-700" />
                <x-input-file type="file" class="" id="manuscript_file" wire:model="manuscript_file" />
                <x-input-error for="manuscript_file" />
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="mt-4">
                    <x-label for="start_page" value="Starting Page Number" class="mb-2 block font-medium text-sm text-gray-700" />
                    <x-input type="text" id="start_page" class="w-full" wire:model="start_page" />
                    <x-input-error for="start_page" />
                </div>
                <div class="mt-4">
                    <x-label for="end_page" value="Ending Page Number" class="mb-2 block font-medium text-sm text-gray-700" />
                    <x-input type="text" id="end_page" class="w-full" wire:model="end_page" />
                    <x-input-error for="end_page" />
                </div>
            </div>

            <div class="mt-4 text-right">
                
                <button type="submit" class="bg-green-500 hover:bg-green-700 rounded-md shadow-sm cursor-pointer p-2 px-4 text-white" wire:click="submitForPublication()" wire:loading.attr="disabled" >
                    {{ __('Submit for Publication') }}
                </button>
            </div>

            @endif
            
        </div>
        
    </div>
    <!-- Backdrop -->
    <div wire:click="closeDrawerG" class="fixed inset-0 bg-black bg-opacity-50 z-40 {{ $isOpenG ? 'block' : 'hidden' }}"></div>


    <x-dialog-modal wire:model="deleteModal">
        <x-slot name="title">
            {{ __('Delete Submission') }}
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <p class="text-center">Are you sure you want to delete this Submission.?</p>
            </div>
        </x-slot>
        <x-slot name="footer">
            
            <x-button type="submit" class="bg-red-500 hover:bg-red-700" wire:click="delete({{ $record }})" wire:loading.attr="disabled" >
                {{ __('Delete') }}
            </x-button>
            <x-secondary-button class="ml-3" wire:click="$toggle('deleteModal')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

        </x-slot>
    </x-dialog-modal>


    <x-dialog-modal wire:model="reviewerFeedback" :maxWidth="'4xl'" >
        <x-slot name="title">
            {{ __('Reviewer Comments') }}
        </x-slot>
        <x-slot name="content">
            
            <div class="mt-4 text-xs">

                @if(!empty($sections))
                    @foreach ($sections as $key => $section)
                
                        <table class="min-w-full text-left font-light">
                            <thead class="border-b font-medium grey:border-neutral-500">
                                <tr class="bg-neutral-200 font-bold">
                                    <th scope="col" class="whitespace-nowrap px-4 py-2 font-bold">
                                        {{ $section->title }}
                                    </th>
                                    @foreach ($section->reviewSectionOption as $key => $option)
                                        <th scope="col" class="whitespace-nowrap px-4 py-2 font-bold text-center">
                                            {{ $option->title }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                
                                @foreach ($section->reviewSectionQuery as $key => $data)
                                <tr class="border-b transition duration-300 ease-in-out @if($section->category == 'options') hover:bg-neutral-100 @endif grey:border-neutral-500 grey:hover:bg-neutral-600">
                                    <td class="whitespace-nowrap px-4 py-2 font-medium">
                                        <p class="w-full @if($section->category == 'comments') font-bold @endif">{{ $data->title }}</p>

                                        @if($section->category == 'comments')
                                        <x-textarea type="text" id="description" class="w-full mt-2" wire:model="reviewComment.{{ $data->id }}" placeholder="Enter Description" rows="5" readonly />
                                        @endif
                                    </td>
                                    @if($section->category == 'options')
                                        @foreach ($section->reviewSectionOption as $key => $option)
                                            <td class="whitespace-nowrap px-4 py-2 font-medium text-center">
                                                <input type="radio" name="option{{ $data->id }}" wire:model.live="reviewOption.{{ $data->id }}" value="{{ $option->id }}" wire:click="upOptions({{ $data->id }}, {{ $option->id }})" disabled />
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    @endforeach
                @endif

            </div>

        </x-slot>
        <x-slot name="footer">

            <x-secondary-button class="ml-3" wire:click="$toggle('reviewerFeedback')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

        </x-slot>
    </x-dialog-modal>


    <x-dialog-modal wire:model="accept_for_production">
        <x-slot name="title">
            {{ __('Accept Manuscript For Production') }}
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <p class="text-center">Are you sure you want to accept and confirm this manuscript submition to Production Stage.?</p>
            </div>
        </x-slot>
        <x-slot name="footer">
            
            <button type="submit" class="bg-green-500 hover:bg-green-700 rounded-md shadow-sm cursor-pointer p-2 px-4 text-white" wire:click="generalReviewStatus()" wire:loading.attr="disabled" >
                {{ __('Confirm Submission') }}
            </button>
            <button class="ml-3 bg-red-500 hover:bg-red-700 rounded-md shadow-sm cursor-pointer p-2 px-4 text-white" wire:click="$toggle('accept_for_production')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </button>

        </x-slot>
    </x-dialog-modal>




    <x-dialog-modal wire:model="confirm_publish">
        <x-slot name="title">
            {{ __('Publish Online First') }}
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <p class="text-center">Are you sure you want to publish online first this manuscript.?</p>
            </div>
        </x-slot>
        <x-slot name="footer">
            
            <button type="submit" class="bg-green-500 hover:bg-green-700 rounded-md shadow-sm cursor-pointer p-2 px-4 text-white" wire:click="publish()" wire:loading.attr="disabled" >
                {{ __('Confirm and Publish') }}
            </button>
            <button class="ml-3 bg-red-500 hover:bg-red-700 rounded-md shadow-sm cursor-pointer p-2 px-4 text-white" wire:click="$toggle('confirm_publish')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </button>

        </x-slot>
    </x-dialog-modal>


    

    <x-dialog-modal wire:model="confirm_xs">
        <x-slot name="title">
            {{ __('Cancel Manuscript Submission') }}
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <p class="text-center">Are you sure you want to cancel this submision.?</p>
            </div>
        </x-slot>
        <x-slot name="footer">
            
            <button type="submit" class="bg-red-500 hover:bg-red-700 rounded-md shadow-sm cursor-pointer p-2 px-4 text-white mr-2" wire:click="cancelSubmision()" wire:loading.attr="disabled" >
                {{ __('Confirm and Cancel') }}
            </button>
            <x-secondary-button class="" wire:click="$toggle('confirm_xs')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

        </x-slot>
    </x-dialog-modal>

</div>


<script>
    window.addEventListener('contentChanged', (e) => {
        tinymce.remove('#description');
        tinymce.init({
            selector: '#description',
            plugins: 'code advlist lists table link',
            
            height: 200,
            skin: false,
            content_css: false,
            advlist_bullet_styles: 'disc,circle,square',
            advlist_number_styles: 'default,lower-alpha,lower-roman,upper-alpha,upper-roman',
            toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media | forecolor backcolor emoticons | code',
            
            setup: function (editor) {
                editor.on('init change', function () {
                    editor.save();
                });
                editor.on('change', function (e) {
                    @this.set('description', editor.getContent());
                });
            },
        });

        tinymce.remove('#editor_comments');
        tinymce.init({
            selector: '#editor_comments',
            plugins: 'code advlist lists table link',
            
            height: 300,
            skin: false,
            content_css: false,
            advlist_bullet_styles: 'disc,circle,square',
            advlist_number_styles: 'default,lower-alpha,lower-roman,upper-alpha,upper-roman',
            toolbar: 'undo redo | styles | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media | forecolor backcolor emoticons | code',
            
            setup: function (editor) {
                editor.on('init change', function () {
                    editor.save();
                });
                editor.on('change', function (e) {
                    @this.set('editor_comments', editor.getContent());
                });
            },
        });
    
    });

</script>