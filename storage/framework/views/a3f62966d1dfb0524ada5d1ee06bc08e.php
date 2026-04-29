<?php echo e(Form::model($attendanceEmployee, ['route' => ['attendanceemployee.update', $attendanceEmployee->id], 'method' => 'PUT'])); ?>

<div class="modal-body">
<div class="row">
    <div class="form-group col-lg-6 col-md-6 ">
        <?php echo e(Form::label('employee_id', __('Employee'), ['class' => 'col-form-label'])); ?>

        <?php echo e(Form::select('employee_id', $employees, null, ['class' => 'form-control select2'])); ?>

    </div>
    <div class="form-group col-lg-6 col-md-6">
        <?php echo e(Form::label('date', __('Date'), ['class' => 'col-form-label'])); ?>

        <?php echo e(Form::date('date', null, ['class' => 'form-control d_week','autocomplete'=>'off'])); ?>

    </div>

    <div class="form-group col-lg-6 col-md-6">
        <?php echo e(Form::label('clock_in', __('Clock In'), ['class' => 'col-form-label'])); ?>

        <?php echo e(Form::time('clock_in', null, ['class' => 'form-control pc-timepicker-2','id'=>'clock_in'])); ?>

        <!-- Hidden fields for location -->
        <?php echo e(Form::hidden('clock_in_latitude', $attendanceEmployee->clock_in_latitude, ['id' => 'clock_in_latitude'])); ?>

        <?php echo e(Form::hidden('clock_in_longitude', $attendanceEmployee->clock_in_longitude, ['id' => 'clock_in_longitude'])); ?>

        <?php echo e(Form::hidden('clock_in_location', $attendanceEmployee->clock_in_location, ['id' => 'clock_in_location'])); ?>

        <small class="text-muted mt-1" id="clock_in_location_text">
            <?php if(!empty($attendanceEmployee->clock_in_location)): ?>
                📍 <?php echo e($attendanceEmployee->clock_in_location); ?>

            <?php endif; ?>
        </small>
    </div>

    <div class="form-group col-lg-6 col-md-6">
        <?php echo e(Form::label('clock_out', __('Clock Out'), ['class' => 'col-form-label'])); ?>

        <?php echo e(Form::time('clock_out', null, ['class' => 'form-control pc-timepicker-2 ','id'=>'clock_out'])); ?>

        <!-- Hidden fields for location -->
        <?php echo e(Form::hidden('clock_out_latitude', $attendanceEmployee->clock_out_latitude, ['id' => 'clock_out_latitude'])); ?>

        <?php echo e(Form::hidden('clock_out_longitude', $attendanceEmployee->clock_out_longitude, ['id' => 'clock_out_longitude'])); ?>

        <?php echo e(Form::hidden('clock_out_location', $attendanceEmployee->clock_out_location, ['id' => 'clock_out_location'])); ?>

        <small class="text-muted mt-1" id="clock_out_location_text">
            <?php if(!empty($attendanceEmployee->clock_out_location)): ?>
                📍 <?php echo e($attendanceEmployee->clock_out_location); ?>

            <?php endif; ?>
        </small>
    </div>
</div>
</div>
<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Edit')); ?>" class="btn btn-primary">
</div>
<?php echo e(Form::close()); ?>


<script>
// Geolocation capture functions
function captureLocation(type) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
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
                    })
                    .catch(error => {
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

// Capture location when time fields are changed
document.getElementById('clock_in').addEventListener('change', function() {
    captureLocation('clock_in');
});

document.getElementById('clock_out').addEventListener('change', function() {
    captureLocation('clock_out');
});
</script>
<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/attendance/edit.blade.php ENDPATH**/ ?>