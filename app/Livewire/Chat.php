<?php
namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $users;
    public $selectedUser;
    public $newMessage;
    public $messages;
    public $loginID;

    public function mount()
    {
        $this->loginID = Auth::id();
        $this->loadUsers();
        $this->selectedUser = $this->users->first();
        $this->loadMessages();
    }

    public function loadUsers()
    {
        $loginId = $this->loginID;
        
        $users = User::whereNot("id", $loginId)->get();
        
        $usersWithMessages = [];
        foreach ($users as $user) {
            $latestMessage = ChatMessage::where(function($q) use ($user, $loginId) {
                $q->where("sender_id", $loginId)
                  ->where("receiver_id", $user->id);
            })
            ->orWhere(function($q) use ($user, $loginId) {
                $q->where("sender_id", $user->id)
                  ->where("receiver_id", $loginId);
            })
            ->latest()
            ->first();
            
            $usersWithMessages[] = [
                'user' => $user,
                'latest_message_time' => $latestMessage ? $latestMessage->created_at : null,
                'latest_message' => $latestMessage ? $latestMessage->message : null,
                'unread_count' => ChatMessage::where('sender_id', $user->id)
                    ->where('receiver_id', $loginId)
                    ->whereNull('read_at')
                    ->count(),
            ];
        }
        
        usort($usersWithMessages, function($a, $b) {
            if ($a['latest_message_time'] && $b['latest_message_time']) {
                return $b['latest_message_time'] <=> $a['latest_message_time'];
            }
            if ($a['latest_message_time']) return -1;
            if ($b['latest_message_time']) return 1;
            
            return $b['user']->created_at <=> $a['user']->created_at;
        });
        
        foreach ($usersWithMessages as $index => $data) {
            $data['user']->latest_message_time = $data['latest_message_time'];
            $data['user']->latest_message = $data['latest_message'];
            $data['user']->unread_count = $data['unread_count'];
        }
        
        $this->users = collect($usersWithMessages)->pluck('user');
    }

    public function selectUser($id)
    {
        $this->selectedUser = User::find($id);
        $this->loadMessages();
        
        ChatMessage::where('sender_id', $id)
            ->where('receiver_id', $this->loginID)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
            
        $this->loadUsers();
    }

    public function loadMessages()
    {
        $this->messages = ChatMessage::query()
                    ->where(function($q){
                        $q->where("sender_id", Auth::id())
                            ->where("receiver_id", $this->selectedUser->id);
                    })
                    ->orWhere(function($q){
                        $q->where("sender_id", $this->selectedUser->id)
                            ->where("receiver_id", Auth::id());
                    })
                    ->orderBy('created_at', 'asc')
                    ->get();
    }

    public function submit()
    {
        if (!$this->newMessage) return;
        
        $message = ChatMessage::create([
            "sender_id" => Auth::id(),
            "receiver_id" => $this->selectedUser->id,
            "message" => $this->newMessage,
        ]);
        
        $this->messages->push($message);
        $this->newMessage = '';
        
        broadcast(new MessageSent($message));
        
        $this->loadUsers();
    }

    public function getListeners()
    {
        return [
            "echo-private:chat.{$this->loginID},MessageSent" => "newChatMessageNotification",
        ];
    }

    public function newChatMessageNotification($eventData)
    {
        $messageData = $eventData;
        
        if ($messageData['sender_id'] == $this->selectedUser->id) {
            $messageObj = ChatMessage::find($messageData['id']);
            $this->messages->push($messageObj);
            
            $messageObj->update(['read_at' => now()]);
        }
        
        $this->loadUsers();
    }

    public function render()
    {
        return view('livewire.chat');
    }
}