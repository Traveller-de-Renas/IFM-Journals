<x-module>
    
    <x-slot name="title">
        {{ __('PERMISSIONS') }}
    </x-slot>


    <div class="w-full grid grid-cols-3 gap-4" >
        <div class="">
            <x-input wire:model.live.debounce.500ms="query" placeholder="search..." type="search" />
        </div>
        <div class=""></div>
        <div class="">
            <x-button class="float-right" wire:click="confirmAdd" wire:loading.attr="disabled" >Create New</x-button>
        </div>
    </div>

    <table class="min-w-full text-left text-sm font-light">
        <thead class="border-b font-medium grey:border-neutral-500">
            <tr>
                <th scope="col" class="px-6 py-4 w-2">#</th>
                <th scope="col" class="px-6 py-4">
                    <button wire:click="sort('name')" >Name</button>
                    <x-sort-icon class="float-right" sortField="name" :sort-by="$sortBy" :sort-asc="$sortAsc" />
                </th>
                <th scope="col" class="px-6 py-4">
                    <button >Roles</button>
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
            @foreach ($permissions as $item)

            <tr class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 grey:border-neutral-500 grey:hover:bg-neutral-600">
                <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $sn }}</td>
                <td class="whitespace-nowrap px-6 py-4">{{ $item->name }}</td>
                <td class="whitespace-nowrap px-6 py-4 text-wrap text-justify">
                    @foreach ($item->roles as $role)
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded">{{ $role->name }}</span>
                    @endforeach
                </td>
                <td class="whitespace-nowrap ">
                    
                    <button id="dropdown{{ $sn }}" data-dropdown-toggle="dropdownDots{{ $sn }}" class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50" type="button">
                        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 4 15">
                        <path d="M3.5 1.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 6.041a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm0 5.959a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                        </svg>
                    </button>
                    
                    <div id="dropdownDots{{ $sn }}" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdown{{ $sn }}">
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100" wire:click="confirmEdit({{ $item->id }})" wire:loading.attr="disabled">Edit</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100" wire:click="confirmDelete({{ $item->id }})" wire:loading.attr="disabled">Delete</a>
                            </li>
                            <li>
                                <a href="#" class="block px-4 py-2 hover:bg-gray-100" wire:click="confirmView({{ $item->id }})" wire:loading.attr="disabled">View</a>
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
        {{ $permissions->links() }}
    </div>

    <x-dialog-modal wire:model="Add">
        <x-slot name="title">
            {{ __('Create New') }}
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <x-label for="name" value="Name" class="mb-2 block font-medium text-sm text-gray-700" />
                <x-input type="text" id="name" class="w-full" wire:model="name" />
                <x-input-error for="name" />
            </div>
        </x-slot>
        <x-slot name="footer">
            
            <x-button type="submit" wire:click="store()" wire:loading.attr="disabled">
                {{ __('Submit') }}
            </x-button>
            <x-secondary-button class="ml-3" wire:click="$toggle('Add')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

        </x-slot>
    </x-dialog-modal>

    <x-dialog-modal wire:model="Edit">
        <x-slot name="title">
            {{ __('Edit Data') }}
        </x-slot>
        <x-slot name="content">
            <div class="mt-4">
                <x-label for="name" value="Name" class="mb-2 block font-medium text-sm text-gray-700" />
                <x-input type="text" id="name" class="w-full" wire:model="name" />
                <x-input-error for="name" />
            </div>
        </x-slot>
        <x-slot name="footer">
            
            <x-button type="submit" wire:click="update({{ $record }})" wire:loading.attr="disabled">
                {{ __('Submit') }}
            </x-button>
            <x-secondary-button class="ml-3" wire:click="$toggle('Edit')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

        </x-slot>
    </x-dialog-modal>


    <x-dialog-modal wire:model="View">
        <x-slot name="title">
            {{ __('View Data') }}
        </x-slot>
        <x-slot name="content">
            @if (session('message'))
                <div class="p-4 rounded-md mb-4 shadow bg-blue-300 w-full">
                    {{ session('message') }}
                </div>
            @endif

            <div class="mt-4">
                <x-label for="name" value="Name" class="mb-2 block font-medium text-sm text-gray-700" />
                <x-input type="text" id="name" class="w-full" wire:model="name" />
                <x-input-error for="name" />
            </div>

            <div class="mt-4">
                <x-label for="role" value="Role" class="mb-2 block font-medium text-sm text-gray-700" />
                <x-select id="role" class="w-full" :options="$roles" wire:model="role" />
                <x-input-error for="role" />
            </div>

            <p class="text-lg text-gray-950 mt-6">
                Note : Click on a specific role to revoke that role from this permission
            </p>


            <div class="mt-4">
                @if ($permission)
                    @foreach ($permission->roles as $role)
                        <x-button type="submit" wire:click="remove_role({{ $role }})" wire:loading.attr="disabled">
                            {{ $role->name }}
                        </x-button>
                    @endforeach
                @endif
                
            </div>

        </x-slot>
        <x-slot name="footer">
            
            <x-button type="submit" wire:click="assign_role({{ $record }})" wire:loading.attr="disabled">
                {{ __('Submit') }}
            </x-button>
            <x-secondary-button class="ml-3" wire:click="$toggle('View')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

        </x-slot>
    </x-dialog-modal>

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
            <x-secondary-button class="ml-3" wire:click="$toggle('Delete')" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

        </x-slot>
    </x-dialog-modal>

</x-module>