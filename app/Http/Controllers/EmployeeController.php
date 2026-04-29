<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Site;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Document;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Mail\UserCreate;
use App\Models\Plan;
use App\Models\User;
use App\Models\Utility;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\JoiningLetter;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;
use App\Models\Contract;
use App\Models\ExperienceCertificate;
use App\Models\LoginDetail;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\NOC;
use App\Models\PaySlip;
use App\Models\Termination;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\DailyQuote;  

//use Faker\Provider\File;

class EmployeeController extends Controller
{
    
    public function index(Request $request)
    {
        if (\Auth::user()->can('Manage Employee')) {
            $query = Employee::where('created_by', \Auth::user()->creatorId());
                
            if (Auth::user()->type == 'employee') {
                $query->where('user_id', Auth::user()->id);
            }
            
            // Filter by employee type if specified
            if ($request->has('employee_type') && !empty($request->employee_type)) {
                $query->where('employee_type', $request->employee_type);
                
                // If employee type is Contract or Payroll and confirmation filter is specified
                if (($request->employee_type === 'Contract' || $request->employee_type === 'Payroll') && $request->has('confirm_employment')) {
                    if ($request->confirm_employment === '1') {
                        // Show only confirmed employees
                        $query->where('confirm_of_employment', true);
                    } elseif ($request->confirm_employment === '0') {
                        // Show only unconfirmed employees
                        $query->where('confirm_of_employment', false);
                    }
                }
            }
            
            if ($request->has('show_left')) {
                $employees = $query->whereNotNull('company_dol')->get();
                $showLeft = true;
            } else {
                $employees = $query->whereNull('company_dol')->get();
                $showLeft = false;
            }
            
            return view('employee.index', compact('employees', 'showLeft'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('Create Employee')) {
            $company_settings = Utility::settings();
            $departments      = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations     = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employees        = User::where('created_by', \Auth::user()->creatorId())->get();
            $employeesId      = \Auth::user()->employeeIdFormat($this->employeeNumber());
            $branches         = Branch::where('created_by', \Auth::user()->creatorId())->get();

            return view('employee.create', compact('employees', 'employeesId', 'departments', 'designations', 'company_settings','branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('Create Employee')) {
            $rules = [
                'name' => 'required',
             
                'email' => 'required|unique:users',
                'password' => 'required',
                'branch_id' => 'nullable|exists:branches,id',
                'department_id' => 'required',
                'designation_id' => 'required',
              
            ];
    
            $validator = \Validator::make($request->all(), $rules);
    
            if ($validator->fails()) {
                return redirect()->back()->withInput()->with('error', $validator->messages()->first());
            }
    
            $validatedData = $validator->validated();
    
            $objUser = User::find(\Auth::user()->creatorId());
            $total_employee = $objUser->countEmployees();
            $plan = Plan::find($objUser->plan);
            $date = now();
    
            if ($total_employee < $plan->max_employees || $plan->max_employees == -1) {
                $user = User::create([
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($validatedData['password']),
                    'type' => 'employee',
                    'created_by' => \Auth::user()->creatorId(),
                    'email_verified_at' => $date,
                ]);
                $user->assignRole('Employee');
            } else {
                return redirect()->back()->with('error', __('Your employee limit is over.'));
            }
    
            // Add created_by to the employee data
            $employeeData = [
                'user_id' => $user->id,
                'name' => $validatedData['name'],
                'dob' => $request->input('dob', null), // or $request->dob ?? null
                'gender' => $request->input('gender', null),
                'phone' => $request->input('phone', null),
                'address' => $request->input('address', null),
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'employee_id' => $this->employeeNumber(),
                'branch_id' => $validatedData['branch_id'],
                'department_id' => $validatedData['department_id'],
                'designation_id' => $validatedData['designation_id'],
                'company_doj' => $request->input('company_doj', null),
                'employee_type' => $request->input('employee_type', null),
                'primary_skill' => $request->input('primary_skill', null),
                'secondary_skill' => $request->input('secondary_skill', null),
                'certificate' => $request->input('certificate', null),
                'esic_no' => $request->input('esic_no', null),
                'bank_ac_no' => $request->input('bank_ac_no', null),
                'project_id' => $request->input('project_id', null),
                'hourly_charged' => $request->input('hourly_charged', null),
                'created_by' => \Auth::user()->creatorId(),
            ];
    
            Employee::create($employeeData);
    
            return redirect()->route('employee.index')->with('success', __('Employee successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    

    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        if (\Auth::user()->can('Edit Employee')) {
            $employee = Employee::find($id);
            $branches = Branch::where('created_by', \Auth::user()->creatorId())->get();
            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            
            return view('employee.edit', compact('employee', 'branches', 'departments', 'designations'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('Edit Employee')) {
            $employee = Employee::findOrFail($id);
            $user = User::findOrFail($employee->user_id);

            $rules = [
                'name' => 'required',
                'dob' => 'required',
                'gender' => 'required',
                'phone' => 'required',
                'address' => 'required',
                'branch_id' => 'nullable|exists:branches,id',
                'department_id' => 'required',
                'designation_id' => 'required',
                'company_doj' => 'required',
                'company_dol' => 'nullable|date|after_or_equal:company_doj',
                'primary_skill' => 'nullable|string',
                'secondary_skill' => 'nullable|string',
                'certificate' => 'nullable|string',
                'project_id' => 'nullable|integer',
                'hourly_charged' => 'nullable|numeric',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->with('error', $validator->messages()->first());
            }

            // Update User
            $userData = [
                'name' => $request->name,
            ];
            
            // Update password only if provided
            if (!empty($request->password)) {
                $userData['password'] = Hash::make($request->password);
            }
            
            $user->update($userData);

            // Determine is_active based on company_dol
            $isActive = empty($request->company_dol) ? 1 : 0;

            // Update Employee
            $employee->update([
                'name' => $request->name,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address,
                'branch_id' => $request->branch_id,
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'company_doj' => $request->company_doj,
                'company_dol' => $request->company_dol,
                'employee_type' => $request->employee_type,
                'is_active' => $isActive,
                'primary_skill' => $request->primary_skill,
                'secondary_skill' => $request->secondary_skill,
                'certificate' => $request->certificate,
                'esic_no' => $request->esic_no,
                'bank_ac_no' => $request->bank_ac_no,
                'project_id' => $request->project_id,
                'hourly_charged' => $request->hourly_charged,
                'created_by' => $employee->created_by,
            ]);

            return redirect()->route('employee.index')->with('success', __('Employee successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    

    public function confirmEmployment(Request $request)
    {
        if (\Auth::user()->can('Edit Employee')) {
            $employeeId = $request->input('employee_id');
            
            try {
                $employee = Employee::findOrFail($employeeId);
                
                // Check if employee is contract or payroll type and not already confirmed
                if (!in_array($employee->employee_type, ['Contract', 'Payroll'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Only contract and payroll employees can be confirmed.')
                    ]);
                }
                
                if ($employee->confirm_of_employment) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Employee is already confirmed.')
                    ]);
                }
                
                // Update the confirmation status
                $employee->update([
                    'confirm_of_employment' => true,
                    'updated_at' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => __('Employment confirmed successfully.')
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => __('An error occurred while confirming employment. Please try again.')
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => __('Permission denied.')
            ]);
        }
    }

    public function cancelEmployment(Request $request)
    {
        if (\Auth::user()->can('Edit Employee')) {
            $employeeId = $request->input('employee_id');
            
            try {
                $employee = Employee::findOrFail($employeeId);
                
                // Check if employee is contract or payroll type and already confirmed
                if (!in_array($employee->employee_type, ['Contract', 'Payroll'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Only contract and payroll employees can have their confirmation cancelled.')
                    ]);
                }
                
                if (!$employee->confirm_of_employment) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Employee is not confirmed yet.')
                    ]);
                }
                
                // Update confirmation status to false
                $employee->update([
                    'confirm_of_employment' => false,
                    'updated_at' => now()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => __('Employment confirmation cancelled successfully.')
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => __('An error occurred while cancelling confirmation. Please try again.')
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => __('Permission denied.')
            ]);
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('Delete Employee')) {
            $employee      = Employee::findOrFail($id);
            $user          = User::where('id', '=', $employee->user_id)->first();
            $emp_documents = EmployeeDocument::where('employee_id', $employee->employee_id)->get();
            $ContractEmployee = Contract::where('employee_name', '=', $employee->user_id)->get();
            $payslips = PaySlip::where('employee_id', $id)->get();
            $employee->delete();
            $user->delete();

            foreach ($ContractEmployee as $contractdelete) {
                $contractdelete->delete();
            }

            foreach ($payslips as $payslip) {
                $payslip->delete();
            }

            $dir = storage_path('uploads/document/');
            foreach ($emp_documents as $emp_document) {

                $emp_document->delete();
                // \File::delete(storage_path('uploads/document/' . $emp_document->document_value));
                if (!empty($emp_document->document_value)) {

                    $file_path = 'uploads/document/' . $emp_document->document_value;
                    $result = Utility::changeStorageLimit(\Auth::user()->creatorId(), $file_path);

                    // unlink($dir . $emp_document->document_value);
                }
            }

            return redirect()->route('employee.index')->with('success', 'Employee successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }



    public function show($id)
    {

        if (\Auth::user()->can('Show Employee')) {
            try {
                $empId        = \Illuminate\Support\Facades\Crypt::decrypt($id);
            } catch (\RuntimeException $e) {
                return redirect()->back()->with('error', __('Employee not avaliable'));
            }
            $documents    = Document::where('created_by', \Auth::user()->creatorId())->get();
            $branches     = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $sites     = Site::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments  = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employee     = Employee::find($empId);
            $employeesId  = \Auth::user()->employeeIdFormat($employee->employee_id);
            $empId        = Crypt::decrypt($id);

            //     $employee     = Employee::find($empId);
            // $branch= Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            return view('employee.show', compact('employee', 'employeesId', 'sites', 'branches', 'departments', 'designations', 'documents'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }



    function employeeNumber()
    {
        $latest = Employee::where('created_by', '=', \Auth::user()->creatorId())->latest('id')->first();
        if (!$latest) {
            return 1;
        }

        return $latest->id + 1;
    }

    public function export()
    {
        $name = 'employee_' . date('Y-m-d i:h:s');
        $data = Excel::download(new EmployeesExport(), $name . '.xlsx');


        return $data;
    }

    public function importFile()
    {
        return view('employee.import');
    }

    // public function import(Request $request)
    // {
    //     $rules = [
    //         'file' => 'required|mimes:csv,txt',
    //     ];

    //     $validator = \Validator::make($request->all(), $rules);

    //     if ($validator->fails()) {
    //         $messages = $validator->getMessageBag();

    //         return redirect()->back()->with('error', $messages->first());
    //     }

    //     $employees = (new EmployeesImport())->toArray(request()->file('file'))[0];
    //     $totalCustomer = count($employees) - 1;
    //     $errorArray    = [];

    //     for ($i = 1; $i <= count($employees) - 1; $i++) {

    //         $employee = $employees[$i];
    //         $employeeByEmail = Employee::where('email', $employee[5])->first();
    //         $userByEmail = User::where('email', $employee[5])->first();

    //         if (!empty($employeeByEmail) && !empty($userByEmail)) {
    //             $employeeData = $employeeByEmail;
    //         } else {
    //             $user = new User();
    //             $user->name = $employee[0];
    //             $user->email = $employee[5];
    //             $user->password = Hash::make($employee[6]);
    //             $user->type = 'employee';
    //             $user->lang = 'en';
    //             $user->created_by = \Auth::user()->creatorId();
    //             $user->email_verified_at = date("Y-m-d H:i:s");
    //             $user->save();
    //             $user->assignRole('Employee');
    //             $employeeData = new Employee();
    //             $employeeData->employee_id      = $this->employeeNumber();
    //             $employeeData->user_id             = $user->id;
    //         }


    //         $employeeData->name                = $employee[0];
    //         $employeeData->dob                 = $employee[1];
    //         $employeeData->gender              = $employee[2];
    //         $employeeData->phone               = $employee[3];
    //         $employeeData->address             = $employee[4];
    //         $employeeData->email               = $employee[5];
    //         $employeeData->password            = \Hash::make($employee[6]);
    //         $employeeData->employee_id         = $this->employeeNumber();
    //         $employeeData->branch_id           = $employee[8];
    //         $employeeData->department_id       = $employee[9];
    //         $employeeData->designation_id      = $employee[10];
    //         $employeeData->company_doj         = $employee[11];
    //         $employeeData->account_holder_name = $employee[12];
    //         $employeeData->account_number      = $employee[13];
    //         $employeeData->bank_name           = $employee[14];
    //         $employeeData->bank_identifier_code = $employee[15];
    //         $employeeData->branch_location     = $employee[16];
    //         $employeeData->tax_payer_id        = $employee[17];
    //         $employeeData->created_by          = \Auth::user()->creatorId();

    //         if (empty($employeeData)) {

    //             $errorArray[] = $employeeData;
    //         } else {

    //             $employeeData->save();
    //         }
    //     }

    //     $errorRecord = [];

    //     if (empty($errorArray)) {
    //         $data['status'] = 'success';
    //         $data['msg']    = __('Record successfully imported');
    //     } else {
    //         $data['status'] = 'error';
    //         $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalCustomer . ' ' . 'record');


    //         foreach ($errorArray as $errorData) {

    //             $errorRecord[] = implode(',', $errorData);
    //         }

    //         \Session::put('errorArray', $errorRecord);
    //     }

    //     return redirect()->back()->with($data['status'], $data['msg']);
    // }

    // public function json(Request $request)
    // {
    //     $designations = Designation::where('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();

    //     return response()->json($designations);
    // }

    public function profile(Request $request)
    {
        if (\Auth::user()->can('Manage Employee Profile')) {
            $employees = Employee::where('created_by', \Auth::user()->creatorId())->with(['designation', 'user']);
            if (!empty($request->branch)) {
                $employees->where('branch_id', $request->branch);
            }
            if (!empty($request->department)) {
                $employees->where('department_id', $request->department);
            }
            if (!empty($request->designation)) {
                $employees->where('designation_id', $request->designation);
            }
            $employees = $employees->get();

            $brances = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $brances->prepend('All', '');

            $departments = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments->prepend('All', '');

            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations->prepend('All', '');

            return view('employee.profile', compact('employees', 'departments', 'designations', 'brances'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function profileShow($id)
    {
        if (\Auth::user()->can('Show Employee Profile')) {
            $empId        = Crypt::decrypt($id);
            $documents    = Document::where('created_by', \Auth::user()->creatorId())->get();
            $branches     = Branch::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $sites        = Site::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $departments  = Department::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $designations = Designation::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $employee     = Employee::find($empId);
            if ($employee == null) {
                $employee     = Employee::where('user_id', $empId)->first();
            }

            $employeesId  = \Auth::user()->employeeIdFormat($employee->employee_id);

            return view('employee.show', compact('employee', 'employeesId', 'sites', 'branches', 'departments', 'designations', 'documents'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function lastLogin(Request $request)
    {
        $users = User::where('created_by', \Auth::user()->creatorId())->get();

        $time = date_create($request->month);
        $firstDayofMOnth = (date_format($time, 'Y-m-d'));
        $lastDayofMonth =    \Carbon\Carbon::parse($request->month)->endOfMonth()->toDateString();
        $objUser = \Auth::user();

        $usersList = User::where('created_by', '=', $objUser->creatorId())
            ->whereNotIn('type', ['super admin', 'company'])->get()->pluck('name', 'id');
        $usersList->prepend('All', '');
        if ($request->month == null) {
            $userdetails = DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where(['login_details.created_by' => \Auth::user()->creatorId()])
                ->whereMonth('date', date('m'))->whereYear('date', date('Y'));
        } else {
            $userdetails = DB::table('login_details')
                ->join('users', 'login_details.user_id', '=', 'users.id')
                ->select(DB::raw('login_details.*, users.id as user_id , users.name as user_name , users.email as user_email ,users.type as user_type'))
                ->where(['login_details.created_by' => \Auth::user()->creatorId()]);
        }
        if (!empty($request->month)) {
            $userdetails->where('date', '>=', $firstDayofMOnth);
            $userdetails->where('date', '<=', $lastDayofMonth);
        }
        if (!empty($request->employee)) {
            $userdetails->where(['user_id'  => $request->employee]);
        }
        $userdetails = $userdetails->get();

        return view('employee.lastLogin', compact('users', 'usersList', 'userdetails'));
    }

    public function employeeJson(Request $request)
    {
        $employees = Employee::where('branch_id', $request->branch)->get()->pluck('name', 'id')->toArray();

        return response()->json($employees);
    }

    public function joiningletterPdf($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $joiningletter = JoiningLetter::where('lang', $currantLang)->where('created_by', \Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $settings = \App\Models\Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);
        $obj = [
            'date' =>  \Auth::user()->dateFormat($date),
            'app_name' => env('APP_NAME'),
            'employee_name' => $employees->name,
            'address' => !empty($employees->address) ? $employees->address : '',
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'start_date' => !empty($employees->company_doj) ? $employees->company_doj : '',
            'branch' => !empty($employees->Branch->name) ? $employees->Branch->name : '',
            'start_time' => !empty($settings['company_start_time']) ? $settings['company_start_time'] : '',
            'end_time' => !empty($settings['company_end_time']) ? $settings['company_end_time'] : '',
            'total_hours' => $result,
        ];

        $joiningletter->content = JoiningLetter::replaceVariable($joiningletter->content, $obj);
        return view('employee.template.joiningletterpdf', compact('joiningletter', 'employees'));
    }
    public function joiningletterDoc($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $joiningletter = JoiningLetter::where('lang', $currantLang)->where('created_by', \Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $settings = \App\Models\Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);



        $obj = [
            'date' =>  \Auth::user()->dateFormat($date),

            'app_name' => env('APP_NAME'),
            'employee_name' => $employees->name,
            'address' => !empty($employees->address) ? $employees->address : '',
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'start_date' => !empty($employees->company_doj) ? $employees->company_doj : '',
            'branch' => !empty($employees->Branch->name) ? $employees->Branch->name : '',
            'start_time' => !empty($settings['company_start_time']) ? $settings['company_start_time'] : '',
            'end_time' => !empty($settings['company_end_time']) ? $settings['company_end_time'] : '',
            'total_hours' => $result,
            //         

        ];
        $joiningletter->content = JoiningLetter::replaceVariable($joiningletter->content, $obj);
        return view('employee.template.joiningletterdocx', compact('joiningletter', 'employees'));
    }

    public function ExpCertificatePdf($id)
    {
        $currantLang = \Cookie::get('LANGUAGE');
        if (!isset($currantLang)) {
            $currantLang = 'en';
        }
        $termination = Termination::where('employee_id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $experience_certificate = ExperienceCertificate::where('lang', $currantLang)->where('created_by', \Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $settings = \App\Models\Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);
        $date1 = date_create($employees->company_doj);
        $date2 = date_create($employees->termination_date);
        $diff  = date_diff($date1, $date2);
        $duration = $diff->format("%a days");

        if (!empty($termination->termination_date)) {

            $obj = [
                'date' =>  \Auth::user()->dateFormat($date),
                'app_name' => env('APP_NAME'),
                'employee_name' => $employees->name,
                'payroll' => !empty($employees->salaryType->name) ? $employees->salaryType->name : '',
                'duration' => $duration,
                'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',

            ];
        } else {
            return redirect()->back()->with('error', __('Termination date is required.'));
        }


        $experience_certificate->content = ExperienceCertificate::replaceVariable($experience_certificate->content, $obj);
        return view('employee.template.ExpCertificatepdf', compact('experience_certificate', 'employees'));
    }
    public function ExpCertificateDoc($id)
    {
        $currantLang = \Cookie::get('LANGUAGE');
        if (!isset($currantLang)) {
            $currantLang = 'en';
        }
        $termination = Termination::where('employee_id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $experience_certificate = ExperienceCertificate::where('lang', $currantLang)->where('created_by', \Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();;
        $settings = \App\Models\Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);
        $date1 = date_create($employees->company_doj);
        $date2 = date_create($employees->termination_date);
        $diff  = date_diff($date1, $date2);
        $duration = $diff->format("%a days");
        if (!empty($termination->termination_date)) {
            $obj = [
                'date' =>  \Auth::user()->dateFormat($date),
                'app_name' => env('APP_NAME'),
                'employee_name' => $employees->name,
                'payroll' => !empty($employees->salaryType->name) ? $employees->salaryType->name : '',
                'duration' => $duration,
                'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',

            ];
        } else {
            return redirect()->back()->with('error', __('Termination date is required.'));
        }

        $experience_certificate->content = ExperienceCertificate::replaceVariable($experience_certificate->content, $obj);
        return view('employee.template.ExpCertificatedocx', compact('experience_certificate', 'employees'));
    }
    public function NocPdf($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $noc_certificate = NOC::where('lang', $currantLang)->where('created_by', \Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $settings = \App\Models\Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);


        $obj = [
            'date' =>  \Auth::user()->dateFormat($date),
            'employee_name' => $employees->name,
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'app_name' => env('APP_NAME'),
        ];

        $noc_certificate->content = NOC::replaceVariable($noc_certificate->content, $obj);
        return view('employee.template.Nocpdf', compact('noc_certificate', 'employees'));
    }
    public function NocDoc($id)
    {
        $users = \Auth::user();

        $currantLang = $users->currentLanguage();
        $noc_certificate = NOC::where('lang', $currantLang)->where('created_by', \Auth::user()->creatorId())->first();
        $date = date('Y-m-d');
        $employees = Employee::where('id', $id)->where('created_by', \Auth::user()->creatorId())->first();
        $settings = \App\Models\Utility::settings();
        $secs = strtotime($settings['company_start_time']) - strtotime("00:00");
        $result = date("H:i", strtotime($settings['company_end_time']) - $secs);


        $obj = [
            'date' =>  \Auth::user()->dateFormat($date),
            'employee_name' => $employees->name,
            'designation' => !empty($employees->designation->name) ? $employees->designation->name : '',
            'app_name' => env('APP_NAME'),
        ];

        $noc_certificate->content = NOC::replaceVariable($noc_certificate->content, $obj);
        return view('employee.template.Nocdocx', compact('noc_certificate', 'employees'));
    }

    public function getdepartment(Request $request)
    {
        if ($request->branch_id == 0) {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        } else {
            $departments = Department::where('created_by', '=', \Auth::user()->creatorId())->where('branch_id', $request->branch_id)->get()->pluck('name', 'id')->toArray();
        }
        return response()->json($departments);
    }

    public function json(Request $request)
    {
        if ($request->department_id == 0) {
            $designations = Designation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id')->toArray();
        }
        $designations = Designation::where('department_id', $request->department_id)->get()->pluck('name', 'id')->toArray();

        return response()->json($designations);
    }

    public function view($id)
    {
        $users = LoginDetail::find($id);
        return view('employee.user_log', compact('users'));
    }

    public function logindestroy($id)
    {
        $employee = LoginDetail::where('user_id', $id)->delete();

        return redirect()->back()->with('success', 'Employee successfully deleted.');
    }
}