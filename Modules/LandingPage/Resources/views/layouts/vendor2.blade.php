@extends('layouts.minimal')

@section('content')
<div class="container">
    <div class="container mx-auto text-center my-4">
        <h2 class="text-2xl font-semibold">Contractor Form</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendor2.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <!-- Contact Details -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3>Contact Details</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" name="address" id="address" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="contact_person">Contact Person</label>
                            <input type="text" class="form-control" name="contact_person" id="contact_person" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_person_phone">Contact Person Phone</label>
                            <input type="text" class="form-control" name="contact_person_phone" id="contact_person_phone" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                        </div>

                        <div class="form-group">
                            <label for="company_website">Company Website</label>
                            <input type="url" class="form-control" name="company_website" id="company_website">
                        </div>

                        <div class="form-group">
                            <label for="experience">Experience</label>
                            <input type="text" class="form-control" name="experience" id="experience">
                        </div>

                        <div class="form-group">
                            <label for="plan_location">Plan - Location</label>
                            <input type="text" class="form-control" name="plan_location" id="plan_location">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Project and Work Info -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><h3>Project & Work Info</h3></div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="project_range">Project Range</label>
                            <input type="text" class="form-control" name="project_range" id="project_range">
                        </div>

                        <div class="form-group">
                            <label for="type_of_work">Type of Work</label>
                            <select name="type_of_work" id="type_of_work" class="form-control" required>
                                <option value="">Select Type of Work</option>
                                @foreach($typeofworks as $typeofwork)
                                    <option value="{{ $typeofwork->name }}">{{ $typeofwork->name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div class="form-group">
                            <label for="accurate_accessible_area">Accurate Accessible Area</label>
                            <textarea class="form-control" name="accurate_accessible_area" id="accurate_accessible_area"></textarea>
                        </div>
                    </div>
                </div>
                 <div class="card mt-5">
                    <div class="card-header"><h3>Last 3 Years Turnover</h3></div>
                    <div class="card-body">
                        @php
                            $currentYear = now()->year;
                            $years = [$currentYear - 1, $currentYear - 2, $currentYear - 3];
                        @endphp

                        @foreach($years as $index => $year)
                            <div class="form-group">
                                <label for="turnover_{{ $index }}">Turnover for {{ $year }}</label>
                                <input type="number" step="0.01" class="form-control" name="turnover[]" id="turnover_{{ $index }}" required>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            

            
        </div>


        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>
@endsection
