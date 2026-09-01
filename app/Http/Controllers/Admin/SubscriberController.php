<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class SubscriberController extends Controller
{

    public function index()
    {
       $subscriber = Subscriber::paginate(basicControl()->paginate);
       return view('admin.subscriber.list', compact('subscriber'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|string|max:255|unique:subscribers',
            ]);

            $subscriber = new Subscriber();
            $subscriber->email = $request->input('email');
            $subscriber->save();
            return back()->with('success', 'You Have Subscribed Successfully');
        }
        catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function destroy($id)
    {
        $data = Subscriber::findOrFail($id);
        $data->delete();

        return back()->with('success', ' Subscriber deleted successfully ');
    }


    public function sendEmailForm()
    {
        $page_title = 'Send Email to Subscribers';
        return view('admin.subscriber.send_email', compact('page_title'));
    }

    public function sendEmail(Request $request)
    {
        // SECURITY: Rate limiting - 5 bulk emails per hour per admin
        $executed = \Illuminate\Support\Facades\RateLimiter::attempt(
            'send-subscriber-email:' . auth()->guard('admin')->id(),
            5, // Max 5 attempts
            function() use ($request) {
                return true;
            },
            3600 // Per hour
        );

        if (!$executed) {
            return back()->with('error', 'Rate limit exceeded. You can only send bulk emails 5 times per hour. Please try again later.');
        }

        $rules = [
            'subject' => 'required|max:255',
            'description' => 'required',
            'confirm' => 'required|accepted', // Require confirmation checkbox
        ];

        $validator = Validator::make($request->all(), $rules, [
            'confirm.accepted' => 'You must confirm that you want to send this email to all subscribers.'
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $basic = basicControl();
        $email_from = $basic->sender_email;
        $requestMessage = $request->message;
        $subject = $request->subject;
        $email_body = $basic->email_description;
        
        if (!Subscriber::first()) {
            return back()->withInput()->with('error', 'No subscribers to send email.');
        }

        $subscribers = Subscriber::all();
        
        // SECURITY: Limit maximum emails per batch
        $maxEmailsPerBatch = 100;
        if ($subscribers->count() > $maxEmailsPerBatch) {
            return back()->with('error', "Too many subscribers ({$subscribers->count()}). Maximum {$maxEmailsPerBatch} emails per batch. Please segment your subscriber list.");
        }

        // SECURITY: Log this bulk email send
        \Illuminate\Support\Facades\Log::warning('Bulk email sent to subscribers', [
            'admin_id' => auth()->guard('admin')->id(),
            'admin_email' => auth()->guard('admin')->user()->email ?? 'unknown',
            'recipient_count' => $subscribers->count(),
            'subject' => $subject,
            'ip_address' => request()->ip(),
            'timestamp' => now(),
        ]);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($subscribers as $subscriber) {
            try {
                $name = explode('@', $subscriber->email)[0];
                $message = str_replace("[[name]]", $name, $email_body);
                $message = str_replace("[[message]]", $requestMessage, $message);
                
                // SECURITY: Removed @ error suppression to catch failures
                Mail::to($subscriber->email)->queue(new SendMail($email_from, $subject, $message));
                $sentCount++;
            } catch (\Exception $e) {
                $failedCount++;
                \Illuminate\Support\Facades\Log::error('Failed to queue email to subscriber', [
                    'subscriber_email' => $subscriber->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = "Email queued for {$sentCount} subscribers.";
        if ($failedCount > 0) {
            $message .= " {$failedCount} failed (check logs).";
        }

        return back()->with('success', $message);
    }

}
