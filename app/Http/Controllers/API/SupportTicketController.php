<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketAttachment;
use App\Models\SupportTicketMessage;
use App\Traits\ApiResponse;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SupportTicketController extends Controller
{
    use ApiResponse,Upload;

    public function ticketList(Request $request)
    {
        try {
            $user = Auth::user();
            $search = $request->all();
            $tickets = SupportTicket::where('user_id', $user->id)
                ->when(isset($search['ticket']), fn ($query) => $query->where('ticket', 'LIKE', "%{$search['ticket']}%"))
                ->when(isset($search['subject']), fn ($query) => $query->where('subject', 'LIKE', "%{$search['subject']}%"))
                ->when(isset($search['status']), fn ($query) => $query->where('status', '=', $search['status']))
                ->latest()->paginate(basicControl()->paginate);

            $statusLabels = [
                0 => 'Open',
                1 => 'Answered',
                2 => 'Replied',
                3 => 'Closed',
            ];

            $formattedData = $tickets->map(function ($ticket) use($statusLabels) {
                return array_merge($ticket->toArray(), [
                    'status' => $statusLabels[$ticket->status],
                    'last_reply' => Carbon::parse($ticket->last_reply)->diffForHumans(),
                ]);
            });
            $data['tickets'] = $formattedData;
            return response()->json($this->withSuccess($data));

        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function ticketView($ticketId)
    {
        try {
            $item = SupportTicket::where('ticket', $ticketId)->where('user_id', auth()->id())
                ->with('messages.attachments')->firstOrFail();

            $formattedMessages = $item->messages->map(function ($message) {
                $attachments = $message->attachments->map(function ($attachment) {
                    static $counter = 1;
                    return [
                        'id' => $attachment->id,
                        'support_ticket_message_id' => $attachment->support_ticket_message_id,
                        'file' => getFile($attachment->driver, $attachment->file),
                        'file_name' => 'File ' . $counter++,
                    ];
                });
                return [
                    'id' => $message->id,
                    'support_ticket_id' => $message->support_ticket_id,
                    'admin_id' => $message->admin_id,
                    'adminImage' => ($message->admin_id != null ?
                        getFile(optional($message->admin)->image_driver, optional($message->admin)->image) : null),
                    'message' => $message->message,
                    'created_at' => dateTime($message->created_at),
                    'updated_at' => dateTime($message->updated_at),
                    'attachments' => $attachments->toArray(),
                ];
            });
            $statusLabels = [
                0 => 'Open',
                1 => 'Answered',
                2 => 'Replied',
                3 => 'Closed',
            ];
            $formattedData = [
                'id' => $item->id,
                'ticket' => $item->ticket,
                'subject' => $item->subject,
                'status' => $statusLabels[$item->status],
                'messages' => $formattedMessages->toArray(),
            ];
            $data['tickets'] = $formattedData;
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function createTicket(Request $request)
    {
        try {
            $user = Auth::user();
            $rules = [
                'images.*' => 'image|mimes:jpg,png,jpeg,pdf|max:2048',
                'subject' => 'required|max:100',
                'message' => 'required',
                'images' => 'max:5',
            ];
            $message = [
                'images.max' => 'Maximum 5 images can be uploaded'
            ];
            $validator = Validator::make($request->all(), $rules,$message);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $newTicket = SupportTicket::create([
                'user_id' => $user->id,
                'ticket' => rand(100000, 999999),
                'subject' => $request->subject,
                'status' => 0,
                'last_reply' => Carbon::now(),
            ]);
            $ticketMsg = SupportTicketMessage::create([
                'support_ticket_id' => $newTicket->id,
                'message' => $request->message
            ]);

            foreach ($request->file('images', []) as $file) {
                $supportFile = $this->fileUpload($file, config('filelocation.ticket.path'), config('filesystems.default'), null, 'webp', 80);
                if (empty($supportFile['path'])) {
                    return response()->json($this->withError('File could not be uploaded'));
                }
                SupportTicketAttachment::create([
                    'support_ticket_message_id' => $ticketMsg->id,
                    'file' => $supportFile['path'],
                    'driver' => $supportFile['driver'] ?? 'local',
                ]);
            }
            $data = 'Ticket created successfully';
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function replyTicket(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $ticket = SupportTicket::find($id);
            if (!$ticket) {
                return response()->json($this->withError('Ticket Not Found.'));
            }
            $rules = [
                'message' => 'required',
                'images.*' => 'image|mimes:jpg,png,jpeg,pdf|max:2048',
                'images' => 'max:5',
            ];
            $message = [
                'images.max' => 'Maximum 5 images can be uploaded'
            ];
            $validator = Validator::make($request->all(), $rules,$message);
            if ($validator->fails()) {
                return response()->json($this->withError(collect($validator->errors())->collapse()));
            }
            $reply = SupportTicketMessage::create([
                'user_id' => $user->id,
                'support_ticket_id' => $ticket->id,
                'message' => $request->message,
                'created_at' => now(),
            ]);
            foreach ($request->file('images', []) as $file) {
                $supportFile = $this->fileUpload($file, config('filelocation.ticket.path'), config('filesystems.default'), null, 'webp', 80);
                if (empty($supportFile['path'])) {
                    return response()->json($this->withError('File could not be uploaded'));
                }
                SupportTicketAttachment::create([
                    'support_ticket_message_id' => $reply->id,
                    'file' => $supportFile['path'],
                    'driver' => $supportFile['driver'] ?? 'local',
                ]);
            }
            $ticket->update([
                'status' => 2,
                'last_reply' => now()
            ]);

            $data = 'Ticket replied successfully';
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }


    public function closeTicket($id)
    {
        try {
            $user = Auth::user();
            $ticket = SupportTicket::find($id);
            if (!$ticket) {
                return response()->json($this->withError('Ticket Not Found.'));
            }
            if ($user->id !== $ticket->user_id) {
                return response()->json($this->withError('Unauthorized. You do not have permission to close this ticket.'));
            }
            if ($ticket->status === 3) {
                return response()->json($this->withError('Ticket is already closed.'));
            }
            $ticket->update([
                'status' => 3,
            ]);
            return response()->json($this->withSuccess('Ticket has been closed'));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }

    public function deleteTicket($ticketId)
    {
        try {
            $userId = auth()->id();
            $ticket = SupportTicket::where('user_id', $userId)->findOrFail($ticketId);
            $ticket->messages()->delete();
            $ticket->delete();
            $data['ticket'] = 'Deleted Successfully';
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withError($e->getMessage()));
        }
    }
}
