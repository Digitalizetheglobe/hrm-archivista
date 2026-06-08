<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SiteVisit;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteVisitController extends Controller
{
    public function index()
    {
        if (Auth::user()->type == 'employee' || Auth::user()->can('Manage Attendance')) {
            if (Auth::user()->type == 'employee') {
                $siteVisits = SiteVisit::where('employee_id', Auth::user()->employee->id)->orderBy('id', 'desc')->get();
            } else {
                $siteVisits = SiteVisit::where('created_by', Auth::user()->creatorId())->orderBy('id', 'desc')->get();
            }

            return view('site_visit.index', compact('siteVisits'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        $employees = Employee::where('created_by', Auth::user()->creatorId())->get()->pluck('name', 'id');
        return view('site_visit.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'location' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $siteVisit = new SiteVisit();
        if (Auth::user()->type == 'employee') {
            $siteVisit->employee_id = Auth::user()->employee->id;
        } else {
            $siteVisit->employee_id = $request->employee_id;
        }
        
        $siteVisit->start_date = $request->start_date;
        $siteVisit->end_date = $request->end_date;
        $siteVisit->location = $request->location;
        $siteVisit->status = 'Pending';
        $siteVisit->created_by = Auth::user()->creatorId();
        $siteVisit->save();

        return redirect()->route('site-visit.index')->with('success', __('Site visit request successfully created.'));
    }

    public function show($id)
    {
        $siteVisit = SiteVisit::find($id);
        return view('site_visit.view', compact('siteVisit'));
    }

    public function approve($id)
    {
        $siteVisit = SiteVisit::find($id);
        $siteVisit->status = 'Approved';
        $siteVisit->approved_by = Auth::user()->id;
        $siteVisit->save();

        // Send Email
        $setings = Utility::settings();
        if (!empty($setings['custom_site_visit_approve_subject']) && !empty($setings['custom_site_visit_approve_body'])) {
            $employee = Employee::find($siteVisit->employee_id);
            if ($employee && !empty($employee->email)) {
                $subject = $setings['custom_site_visit_approve_subject'];
                $body = $setings['custom_site_visit_approve_body'];

                // Replace placeholders in Body
                $body = str_replace('{employee_name}', $employee->name, $body);
                $body = str_replace('{status}', 'Approved', $body);
                $body = str_replace('{location}', $siteVisit->location, $body);
                $body = str_replace('{start_date}', $siteVisit->start_date, $body);
                $body = str_replace('{end_date}', $siteVisit->end_date, $body);
                $body = str_replace('{app_name}', env('APP_NAME'), $body);

                // Replace placeholders in Subject
                $subject = str_replace('{employee_name}', $employee->name, $subject);
                $subject = str_replace('{status}', 'Approved', $subject);
                $subject = str_replace('{location}', $siteVisit->location, $subject);
                $subject = str_replace('{start_date}', $siteVisit->start_date, $subject);
                $subject = str_replace('{end_date}', $siteVisit->end_date, $subject);
                $subject = str_replace('{app_name}', env('APP_NAME'), $subject);

                $dummyTemplate = new \stdClass();
                $dummyTemplate->subject = $subject;
                $dummyTemplate->content = '<div style="font-size: 14px; font-family: \'Open Sans\', sans-serif; color: #333; line-height: 1.6;">' . nl2br($body) . '</div>';

                try {
                    $mailSettings = Utility::getSMTPDetails(\Auth::user()->creatorId());
                    if ($mailSettings) {
                        \Mail::to([$employee->email])->send(new \App\Mail\CommonEmailTemplate($dummyTemplate, $mailSettings, $employee->email));
                        $resp = ['is_success' => true];
                    } else {
                        $resp = ['is_success' => false, 'error' => 'SMTP details not found'];
                    }
                } catch (\Exception $e) {
                    $resp = ['is_success' => false, 'error' => $e->getMessage()];
                }
            }
        }

        return redirect()->route('site-visit.index')->with('success', __('Site visit request approved.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
    }

    public function reject($id)
    {
        $siteVisit = SiteVisit::find($id);
        $siteVisit->status = 'Rejected';
        $siteVisit->save();

        return redirect()->route('site-visit.index')->with('success', __('Site visit request rejected.'));
    }

    public function destroy($id)
    {
        $siteVisit = SiteVisit::find($id);
        $siteVisit->delete();

        return redirect()->route('site-visit.index')->with('success', __('Site visit request deleted.'));
    }

    public function saveCustomEmail(Request $request)
    {
        $user = \Auth::user();
        if ($user->type == 'company') {
            $post = [
                'custom_site_visit_approve_subject' => $request->subject,
                'custom_site_visit_approve_body' => $request->body,
            ];
            foreach ($post as $key => $data) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                    [
                        $data,
                        $key,
                        $user->creatorId(),
                    ]
                );
            }
            return redirect()->back()->with('success', __('Custom Email Template successfully saved.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}
