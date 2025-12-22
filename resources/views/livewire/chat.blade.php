<div>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Inbox') }}</flux:heading>
        <flux:separator variant="subtle" />
    </div>

    <div class="flex h-[550px] text-sm border rounded-xl shadow overflow-hidden bg-white dark:bg-zinc-800 dark:border-zinc-700">
        <!-- Left: User List -->
        <div class="w-1/4 border-r bg-gray-50 dark:bg-zinc-900 dark:border-zinc-700">
            <div class="p-4 font-bold text-gray-700 dark:text-gray-300 border-b dark:border-zinc-700">Users</div>
            <div class="divide-y dark:divide-zinc-700">
                @foreach ($users as $user)
                    <div wire:click="selectUser({{ $user->id }})" 
                        class="p-3 cursor-pointer hover:bg-blue-100 dark:hover:bg-zinc-700 transition
                    {{ $selectedUser->id === $user->id ? 'bg-blue-50 dark:bg-zinc-800 font-semibold' : '' }}">
                        <div class="text-gray-800 dark:text-gray-200">{{ $user->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    
        <!-- Right: Chat Section -->
        <div class="w-3/4 flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b bg-gray-50 dark:bg-zinc-900 dark:border-zinc-700">
                <div class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $selectedUser->name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedUser->email }}</div>
            </div>
    
            <!-- Messages -->
            <div class="flex-1 p-4 overflow-y-auto space-y-2 bg-gray-50 dark:bg-zinc-800">
                @foreach ($messages as $message)
                    <div class="flex {{ $message->sender_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs px-4 py-2 rounded-2xl shadow
                        {{ $message->sender_id === Auth::id() 
                            ? 'bg-blue-600 text-white dark:bg-blue-700' 
                            : 'bg-gray-200 text-gray-800 dark:bg-zinc-700 dark:text-gray-200' }}">
                            {{ $message->message }}
                        </div>
                    </div>
                @endforeach
            </div>
    
            <!-- Input -->
            <form wire:submit="submit" class="p-4 border-t bg-white dark:bg-zinc-900 dark:border-zinc-700 flex items-center gap-2">
                <input 
                    wire:model="newMessage"
                    type="text"
                    class="flex-1 border border-gray-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-600 focus:border-transparent"
                    placeholder="Type your message..." />
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800 text-white text-sm px-4 py-2 rounded-full transition">
                    Send
                </button>
            </form>
        </div>
    </div>
</div>