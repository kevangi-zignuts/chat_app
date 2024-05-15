<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Message;
use Livewire\Component;
use App\Events\SendMessage;
use Livewire\Attributes\On;

class Chat extends Component
{
    public $user, $sender_id, $receiver_id;
    public $message = '';
    public $messages = [];

    public function render()
    {
        return view('livewire.chat');
    }

    public function mount($user_id){
        $this->sender_id = auth()->user()->id;
        $this->receiver_id = $user_id;

        $messages = Message::where(function ($query) {
            $query->where('sender_id', $this->sender_id)
                ->where('receiver_id', $this->receiver_id);
        })->orWhere(function ($query) {
            $query->where('sender_id', $this->receiver_id)
                ->where('receiver_id', $this->sender_id);
        })->with('sender', 'receiver')->orderBy('id', 'asc')->get();

        foreach ($messages as $message) {
            $this->appendChatMessage($message);
        }

        $this->user = User::findOrFail($user_id);
    }
    public function sendMessage(){
        $chatMessage = Message::create([
            'sender_id'   => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'message'     => $this->message,
        ]);

        broadcast(new SendMessage($chatMessage))->toOthers();
        $this->appendChatMessage($chatMessage);

        $this->message = '';
    }

    #[On('echo-private:chat-channel.{sender_id},SendMessage')]
    public function listenForMessage($event){
        $message = Message::whereId($event['message']['id'])->with('sender', 'receiver')->first();
        $this->appendChatMessage($message);
    }

    public function appendChatMessage($message)
    {
        $this->messages[] = [
            'id' => $message->id,
            'message' => $message->message,
            'sender'   => $message->sender->name,
            'senderId'   => $message->sender->id,
            'receiver' => $message->receiver->name,
            'receiverId' => $message->receiver->id,
        ];
    }
}
