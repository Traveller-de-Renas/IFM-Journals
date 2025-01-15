<div>
    <div class="bg-gray-800 text-white bg-blend-overlay py-4" style="background-image: url({{ asset('images/auth-image.jpg') }}); background-position: top;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-12 gap-2 ">
                
                <div class="col-span-10 w-full mb-2 mt-2">
                    <p class="text-4xl font-bold">
                        {{ __($record->title) }} ({{ strtoupper($record->code) }})
                    </p>
    
                    <div class="mb-2">
                        <div class="text-sm">
                            {{ $record->chief_editor?->salutation?->title }} {{ $record->chief_editor?->first_name }} {{ $record->chief_editor?->middle_name }} {{ $record->chief_editor?->last_name }} 
                            {{ $record->chief_editor?->affiliation != '' ? '('. $record->chief_editor?->affiliation.')' : '' }}
                        </div>
                    </div>
    
                    <br>
    
                    <div class="grid grid-cols-12 gap-2">
                        <p class="col-span-2 text-sm font-bold">ISSN </p>
                        <p class="col-span-10">: {{ $record->issn }}</p>
                    </div>
    
                    <div class="grid grid-cols-12 gap-2">
                        <p class="col-span-2 text-sm font-bold">EISSN </p>
                        <p class="col-span-10">: {{ $record->eissn }}</p>
                    </div>
    
                    <div class="grid grid-cols-12 gap-2">
                        <p class="col-span-2 text-sm font-bold">EMAIL </p>
                        <p class="col-span-10">: {{ $record->email }}</p>
                    </div>
    
                    <div class="grid grid-cols-12 gap-2">
                        <p class="col-span-2 text-sm font-bold">CREATED </p>
                        <p class="col-span-10">: {{ $record->created_at }}</p>
                    </div>

                    <div class="grid grid-cols-12 gap-2">
                        <p class="col-span-2 text-sm font-bold">CURRENT VOLUME </p>
                        <p class="col-span-10">: {{ $record->volume?->description }}</p>
                    </div>

                    <div class="grid grid-cols-12 gap-2">
                        <p class="col-span-2 text-sm font-bold">CURRENT ISSUE </p>
                        <p class="col-span-10">: {{ $record->issue?->description }}</p>
                    </div>
    
                    <br>
                    <hr>
    
                    <div class="w-full text-justify mt-4 mb-4">
                        {!! $record->description !!}
                    </div>
    
                    
                </div>
                <div class="col-span-2">
                    @if($record->image == '')
                        <div class="p-2">
                            <svg class="w-full text-white dark:text-gray-600" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                                <path d="M18 0H2a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2Zm-5.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm4.376 10.481A1 1 0 0 1 16 15H4a1 1 0 0 1-.895-1.447l3.5-7A1 1 0 0 1 7.468 6a.965.965 0 0 1 .9.5l2.775 4.757 1.546-1.887a1 1 0 0 1 1.618.1l2.541 4a1 1 0 0 1 .028 1.011Z"/>
                            </svg>
                        </div>
                    @else
                        <img class="w-full rounded-md rounded-bl-md mt-4" src="{{ asset('storage/journals/'.$record->image) }}" alt="{{ strtoupper($record->code) }}">
                    @endif
    
                    <a href="{{ route('journals.submission', $record->uuid) }}">
                        <x-button class="mb-4 mt-2 w-full">Submit a Paper </x-button>
                    </a>
                </div>
    
            </div>
        </div>
    </div>
    
    @if (session('success'))
        <div class="p-4 text-sm mb-4 shadow bg-green-300 w-full text-center">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8 mb-8">
        <div class="flex w-full border-red-200 gap-4 justify-center">
            <x-button class="w-1/5" wire:click="createVolume();">Create New Volume </x-button>
            <x-button class="w-1/5" wire:click="createIssue();">Create New Issue </x-button>
        </div>
    </div>

    <hr>

    <div class=" w-full ">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-2">

            @if ($record->volumes != '')

                <div class="w-full mb-4">
                    
                    
                        
                    


                    <div id="accordion-collapse" data-accordion="collapse">
                        @foreach ($record->volumes as $key => $volume)
                            <h2 id="accordion-collapse-heading-{{ $key}}">
                                <button type="button" class="flex items-center justify-between w-full p-2 font-medium rtl:text-right text-gray-500 border border-b-0 border-gray-200 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3" data-accordion-target="#accordion-collapse-body-{{ $key}}" aria-expanded="true" aria-controls="accordion-collapse-body-1">
                                    <span>{{ $volume->description }}</span>
                                    <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
                                    </svg>
                                </button>
                            </h2>
                            <div id="accordion-collapse-body-{{ $key}}" class="hidden" aria-labelledby="accordion-collapse-heading-{{ $key}}">
                                <div class="p-2 border border-b-0 border-gray-200 dark:border-gray-700 dark:bg-gray-900">

                                    <div id="accordion-collapse-issues-{{ $key}}" data-accordion="collapse">
                                        @foreach ($volume->issues as $key => $issue)
                                            <h2 id="accordion-collapse-heading-issues-{{ $key}}">
                                                <button type="button" class="flex items-center justify-between w-full p-2 font-medium rtl:text-right text-gray-500 border border-b-0 border-gray-200 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3" data-accordion-target="#accordion-collapse-body-issues-{{ $key}}" aria-expanded="true" aria-controls="accordion-collapse-body-1">
                                                    <span>{{ $issue->description }}</span>
                                                    <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5"/>
                                                    </svg>
                                                </button>
                                            </h2>
                                            <div id="accordion-collapse-body-issues-{{ $key}}" class="hidden" aria-labelledby="accordion-collapse-heading-issues-{{ $key}}">
                                                <div class="p-5 border border-b-0 border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                                                    @if(auth()->user()?->id == $record->chief_editor?->id || auth()->user()->hasRole('Administrator'))
                                                        @if($issue->status == 'Unpublished')
                                                            <x-button class="" wire:click="publishIssue({{ $issue->id }}, 'Published')">Publish Issue</x-button>
                                                        @endif

                                                        @if($issue->status == 'Published')
                                                            <x-button-plain class="bg-red-700 hover:bg-red-500" wire:click="publishIssue({{ $issue->id }}, 'Unpublished')">Unpublish Issue</x-button-plain>
                                                        @endif
                                                    @endif

                                                    @foreach ($issue->articles as $key => $article)
                                                    <div class="w-full mb-6">
                                                        <a href="{{ route('journal.article', $article->uuid) }}">
                                                        <p class="text-blue-700 hover:text-blue-500 text-lg font-bold cursor-pointer">{{ $article->title }}</p>
                                                        </a>
                                                        
                                                        <div class="text-sm text-green-700">
                                                            {{ $article->author?->salutation?->title }} {{ $article->author?->first_name }} {{ $article->author?->middle_name }} {{ $article->author?->last_name }} 
                                                            {{ $article->author?->affiliation != '' ? '('. $article->author?->affiliation.')' : '' }}
                                                        </div>

                                                        <div class="mt-2 text-justify">
                                                            {!! $article->abstract !!}
                                                        </div>
                                                    </div>
                                                    @endforeach

                                                </div>
                                            </div>
                                        @endforeach
                                    </div>


                                </div>
                            </div>
                        @endforeach
                    </div>
  
                    
                </div>

            @else
            
            @endif

        </div>
    </div>

    <x-dialog-modal wire:model="deleteModal">
        <x-slot name="title">
            {{ __('Delete Data') }}
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <p class="text-center">Are you sure you want to delete this record.?</p>
            </div>
        </x-slot>
        <x-slot name="footer">
            
            <x-button-danger type="submit" wire:click="delete({{ $article?->id }})" wire:loading.attr="disabled" >
                {{ __('Delete') }}
            </x-button-danger>
            <x-secondary-button class="ml-3" wire:click="$toggle('deleteModal')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

        </x-slot>
    </x-dialog-modal>
</div>