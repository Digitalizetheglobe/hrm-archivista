{{ Form::open(['url' => 'attendanceemployee', 'method' => 'post']) }}
<div class="card-body p-0">
    <div class="row">
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'col-form-label']) }}
            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select2']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('date', __('Date'), ['class' => 'col-form-label']) }}
            {{ Form::text('date', null, ['class' => 'form-control d_week','autocomplete'=>'off']) }}
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('clock_in', __('Clock In'), ['class' => 'col-form-label']) }}
            {{ Form::text('clock_in', null, ['class' => 'form-control timepicker', 'id' => 'clock_in']) }}
            <!-- Hidden fields for location -->
            {{ Form::hidden('clock_in_latitude', '', ['id' => 'clock_in_latitude']) }}
            {{ Form::hidden('clock_in_longitude', '', ['id' => 'clock_in_longitude']) }}
            {{ Form::hidden('clock_in_location', '', ['id' => 'clock_in_location']) }}
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-info" onclick="captureLocation('clock_in')">
                    <i class="ti ti-map-pin"></i> Capture Location
                </button>
                <small class="text-muted ms-2" id="clock_in_location_text"></small>
            </div>
        </div>
        <div class="form-group col-lg-6 col-md-6">
            {{ Form::label('clock_out', __('Clock Out'), ['class' => 'col-form-label']) }}
            {{ Form::text('clock_out', null, ['class' => 'form-control timepicker', 'id' => 'clock_out']) }}
            <!-- Hidden fields for location -->
            {{ Form::hidden('clock_out_latitude', '', ['id' => 'clock_out_latitude']) }}
            {{ Form::hidden('clock_out_longitude', '', ['id' => 'clock_out_longitude']) }}
            {{ Form::hidden('clock_out_location', '', ['id' => 'clock_out_location']) }}
            <div class="mt-2">
                <button type="button" class="btn btn-sm btn-outline-info" onclick="captureLocation('clock_out')">
                    <i class="ti ti-map-pin"></i> Capture Location
                </button>
                <small class="text-muted ms-2" id="clock_out_location_text"></small>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer pr-0">
    <button type="button" class="btn dark btn-outline" data-dismiss="modal">{{ __('Cancel') }}</button>
    {{ Form::submit(__('Create'), ['class' => 'btn btn-primary']) }}
</div>
{{ Form::close() }}

<script>
// Geolocation capture functions
function captureLocation(type) {
    console.log('Capturing location for:', type);
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                console.log('Location captured:', lat, lng);
                
                // Set hidden field values
                document.getElementById(type + '_latitude').value = lat;
                document.getElementById(type + '_longitude').value = lng;
                
                // Get address using reverse geocoding (optional)
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        const address = data.display_name || `Lat: ${lat}, Lng: ${lng}`;
                        document.getElementById(type + '_location').value = address;
                        document.getElementById(type + '_location_text').textContent = '📍 ' + address;
                        console.log('Address resolved:', address);
                    })
                    .catch(error => {
                        console.error('Geocoding error:', error);
                        // Fallback to coordinates
                        const address = `Lat: ${lat}, Lng: ${lng}`;
                        document.getElementById(type + '_location').value = address;
                        document.getElementById(type + '_location_text').textContent = '📍 ' + address;
                    });
            },
            function(error) {
                console.error('Error getting location:', error);
                document.getElementById(type + '_location_text').textContent = '❌ Location access denied';
            }
        );
    } else {
        document.getElementById(type + '_location_text').textContent = '❌ Geolocation not supported';
    }
}

// Auto-capture location when time fields are changed
document.addEventListener('DOMContentLoaded', function() {
    const clockInField = document.getElementById('clock_in');
    const clockOutField = document.getElementById('clock_out');
    
    if (clockInField) {
        clockInField.addEventListener('change', function() {
            captureLocation('clock_in');
        });
    }
    
    if (clockOutField) {
        clockOutField.addEventListener('change', function() {
            captureLocation('clock_out');
        });
    }
});
</script>
