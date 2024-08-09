<x-module>
    
    <x-slot name="title">
        {{ __('ACTIVITY LOG') }}
    </x-slot>


    <div class="w-full grid grid-cols-3 gap-4" >
        <div class="">
            <x-input wire:model.live.debounce.500ms="query" placeholder="search..." type="search" />
        </div>
        <div class=""></div>
        <div class="text-right">
        </div>
    </div>

    <table class="min-w-full text-left text-sm font-light">
        <thead class="border-b font-medium grey:border-neutral-500">
            <tr>
                <th scope="col" class="px-6 py-4 w-2">#</th>
                <th scope="col" class="px-6 py-4">
                    <button wire:click="sort('description')" >Description</button>
                    <x-sort-icon class="float-right" sortField="description" :sort-by="$sortBy" :sort-asc="$sortAsc" />
                </th>
                <th scope="col" class="py-4 w-2" >
                    Actions
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $sn = 1;
            @endphp
            @foreach ($users as $item)

            <tr class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 grey:border-neutral-500 grey:hover:bg-neutral-600">
                <td class="whitespace-nowrap px-6 py-4 font-medium align-top">{{ $sn }}</td>
                <td class="whitespace-nowrap px-6 py-4 text-wrap">
                    <div class="grid grid-cols-12 text-xs text-blue-700">
                        <div class="col-span-2">Date, Time </div>
                        <div class="col-span-10"> {{ $item->created_at }} </div>
                    </div>

                    <div class="grid grid-cols-12 text-xs text-red-600">
                        <div class="col-span-2">Causer </div> 
                        <div class="col-span-10"> 
                            @if($item->causer != '')
                            {{ $item->causer->name }} 
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-12 text-xs text-green-600">
                        <div class="col-span-2">Description </div> 
                        <div class="col-span-10"> {{ $item->description }} </div>
                    </div>
                    
                    @foreach($item->properties as $key => $value)
                        <div class="text-xs text-orange-500 mt-2 border-b">{{ ucwords($key) }}</div>
                        @foreach($value as $keyx => $valuex)
                        <div class="grid grid-cols-12 text-xs">
                            <div class="col-span-2">{{ $keyx }} </div> 
                            <div class="col-span-10 text-wrap"> {{ $valuex }} </div>
                        </div>
                        @endforeach

                    @endforeach
                </td>
                <td class="whitespace-nowrap px-6 py-4 align-top">
                    
                    <button id="dropdown{{ $item->id }}" data-dropdown-toggle="dropdownDots{{ $item->id }}" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50 " type="button">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                        <path d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                        </svg>
                    </button>
                        
                    <div id="dropdownDots{{ $item->id }}" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 ">
                        <ul class="py-2 text-sm text-gray-700 " aria-labelledby="dropdown{{ $item->id }}">
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100 " wire:click="confirmView({{ $item->id }})" wire:loading.attr="disabled">View</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100 " wire:click="confirmDelete({{ $item->id }})" wire:loading.attr="disabled">Delete</a>
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
        {{ $users->links() }}
    </div>


    <x-dialog-modal wire:model="Delete">
        <x-slot name="title">
            {{ __('Delete Data') }}
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <p class="text-center">Are you sure you want to delete this record.?</p>
            </div>
        </x-slot>
        <x-slot name="footer">
            
            <x-button-danger type="submit" wire:click="delete({{ $record }})" wire:loading.attr="disabled" >
                {{ __('Delete') }}
            </x-button-danger>
            <x-secondary-button class="ml-3" wire:click="$toggle('Edit')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

        </x-slot>
    </x-dialog-modal>


    <x-dialog-modal wire:model="View">
        <x-slot name="title">
            {{ __('User Activity Logs') }}
        </x-slot>
        <x-slot name="content">
            @if ($user != '')
            
            <div class="w-full mt-2">
                @foreach ($user->roles as $role)
                    <x-danger-button class="ml-3" wire:click="remove_role({{ $role->id }})" wire:loading.attr="disabled">
                        {{ __($role->name) }}
                    </x-danger-button>
                @endforeach
            </div>
            
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingView')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>

</x-module>