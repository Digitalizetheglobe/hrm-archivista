<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        if (\Auth::user()->can('Manage Branch')) {
            $sites = Site::where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('site.index', compact('sites'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('Create Branch')) {
            return view('site.create');
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Branch')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $site = new Site();
            $site->name = $request->name;
            $site->created_by = \Auth::user()->creatorId();
            $site->save();

            return redirect()->route('site.index')->with('success', __('Site successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit(Site $site)
    {
        if (\Auth::user()->can('Edit Branch')) {
            if ($site->created_by == \Auth::user()->creatorId()) {
                return view('site.edit', compact('site'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, Site $site)
    {
        if (\Auth::user()->can('Edit Branch')) {
            if ($site->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(),
                    ['name' => 'required']
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $site->name = $request->name;
                $site->save();

                return redirect()->route('site.index')->with('success', __('Site successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy(Site $site)
    {
        if (\Auth::user()->can('Delete Branch')) {
            if ($site->created_by == \Auth::user()->creatorId()) {
                $site->delete();
                return redirect()->route('site.index')->with('success', __('Client Group successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
