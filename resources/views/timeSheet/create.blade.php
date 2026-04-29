    {{ Form::open(['route' => ['timesheet.store']]) }}

    <div class="modal-body px-50">
        <div class="row">

        @if (\Auth::user()->type != 'employee')
            @if(isset($employees) && count($employees) > 0)
                <div class="form-group col-md-6">
                    {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
                    {!! Form::select('employee_id', $employees, null, [
                        'class' => 'form-control select',
                        'required' => 'required',
                        'id' => 'employee_id'
                    ]) !!}
                </div>
            @endif
        @endif

            <div class="form-group col-md-6">
                {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
                {{ Form::text('date', '', [
                    'class' => 'form-control d_week current_date',
                    'autocomplete' => 'off',
                    'required' => 'required',
                    'placeholder' => 'Select date'
                ]) }}
            </div>

            <div class="form-group col-md-6">
                {{ Form::label('client_id', __('Client'), ['class' => 'col-form-label']) }}
                {!! Form::select('client_id', $clients, null, [
                    'class' => 'form-control select2',  
                    'required' => 'required',
                    'placeholder' => 'Select client',
                    'id' => 'client_id'
                ]) !!}
            </div>

            <div class="form-group col-md-6">
                {{ Form::label('project_id', __('Project / Job'), ['class' => 'col-form-label']) }}
                {!! Form::select('project_id', [], null, [
                    'class' => 'form-control select',
                    'required' => 'required',
                    'placeholder' => 'Select project/job',
                    'id' => 'project_id'
                ]) !!}

            </div>

            <!-- Rest of your form fields (time, billable, etc.) -->
            <div class="form-group col-md-6">
                {{ Form::label('total_time', __('Total Time (HH:MM)'), ['class' => 'col-form-label']) }}
                {{ Form::text('total_time', null, [
                    'class' => 'form-control timepicker',
                    'required' => 'required',
                    'placeholder' => 'HH:MM'
                ]) }}
            </div>

            <div class="form-group col-md-6">
                {{ Form::label('billable', __('Billable Status'), ['class' => 'col-form-label']) }}
                {!! Form::select('billable', ['Billable' => 'Billable', 'Non-Billable' => 'Non-Billable'], null, [
                    'class' => 'form-control select',
                    'required' => 'required'
                ]) !!}
            </div>

            <div class="form-group col-md-6">
                {{ Form::label('location', __('Location'), ['class' => 'col-form-label']) }}
                {{ Form::text('location', null, ['class' => 'form-control', 'placeholder' => 'Enter location']) }}
            </div>

            <div class="form-group col-md-6">
                {{ Form::label('expense', __('Expense'), ['class' => 'col-form-label']) }}
                {{ Form::number('expense', 0, [
                    'class' => 'form-control',
                    'step' => '0.01',
                    'min' => '0',
                    'placeholder' => 'Enter expense amount'
                ]) }}
            </div>

            <div class="form-group col-md-12">
                {{ Form::label('narration', __('Narration'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('narration', null, [
                    'class' => 'form-control',
                    'rows' => '3',
                    'placeholder' => 'Enter work description'
                ]) }}
            </div>

            <!-- Rest of your form fields remain the same -->
            <!-- ... -->

        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Close') }}</button>
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>

    {{ Form::close() }}

    <script>
    $(document).ready(function() {
        // Initialize date
        var now = new Date();
        var month = (now.getMonth() + 1).toString().padStart(2, '0');
        var day = now.getDate().toString().padStart(2, '0');
        var today = now.getFullYear() + '-' + month + '-' + day;
        $('.current_date').val(today);

        // Initialize select
        $('.select').select();

        $('#client_id').change(function() {
            var clientId = $(this).val();
            
            // Debugging
            console.log("Selected Client ID:", clientId);
            
            if (clientId) {
                $('#project_id').html('<option value="">Loading...</option>');
                
                $.ajax({
                    url: 'get-client-projects/' + clientId,  // Added /hrm/ prefix
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log("Received projects data:", data);
                        
                        var options = '<option value="">Select Project</option>';
                        
                        if (Object.keys(data).length > 0) {
                            $.each(data, function(id, name) {
                                options += '<option value="'+id+'">'+name+'</option>';
                            });
                        } else {
                            options = '<option value="">No projects found</option>';
                        }
                        
                        $('#project_id').html(options).trigger('change'); // Added trigger change
                    },
                    error: function(xhr) {
                        console.error("Error:", xhr.status, xhr.responseText);
                        $('#project_id').html('<option value="">Error loading projects</option>');
                    }
                });
            } else {
                $('#project_id').html('<option value="">Select Project</option>');
            }
        });
    });
    </script>

    @section('scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: 'Select client',
                allowClear: true
            });
        });
    </script>
    @endse