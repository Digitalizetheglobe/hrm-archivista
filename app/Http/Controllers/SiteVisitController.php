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
                $siteVisits = SiteVisit::where('employee_id', Auth::user()->employee->id)->get();
            } else {
                $siteVisits = SiteVisit::where('created_by', Auth::user()->creatorId())->get();
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
                'date' => 'required|date',
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
        
        $siteVisit->date = $request->date;
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

        return redirect()->route('site-visit.index')->with('success', __('Site visit request approved.'));
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
}
