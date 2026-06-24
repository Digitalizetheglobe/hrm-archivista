<table>
    <tr></tr>
    <tr>
        <td style="font-weight: bold;"><?php echo e($start_date); ?> To <?php echo e($end_date); ?></td>
        <?php for($i = 0; $i < count($days); $i++): ?>
            <td></td>
        <?php endfor; ?>
        <td colspan="8" style="font-weight: bold; text-align: center;">Summary</td>
    </tr>
    <tr>
        <?php for($i = 0; $i <= count($days); $i++): ?>
            <td></td>
        <?php endfor; ?>
        <td style="font-weight: bold;">Monthly Days</td>
        <td style="font-weight: bold;">Total Present Days</td>
        <td style="font-weight: bold;">Early Leaving</td>
        <td style="font-weight: bold;">Half Day</td>
        <td style="font-weight: bold;">Total LWP</td>
        <td style="font-weight: bold;">Week Off</td>
        <td style="font-weight: bold;">Total Leave</td>
        <td style="font-weight: bold;">Total Payable Days</td>
    </tr>

    <?php $__currentLoopData = $reportData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td style="font-weight: bold;">Employee Code: <?php echo e($data['employee']->id); ?></td>
            <?php for($i = 0; $i < count($days); $i++): ?>
                <td></td>
            <?php endfor; ?>
            <td><?php echo e($data['summary']['monthly_days']); ?></td>
            <td><?php echo e($data['summary']['present']); ?></td>
            <td><?php echo e($data['summary']['early_leaving']); ?></td>
            <td><?php echo e($data['summary']['half_day']); ?></td>
            <td><?php echo e($data['summary']['lwp']); ?></td>
            <td><?php echo e($data['summary']['week_off']); ?></td>
            <td><?php echo e($data['summary']['leave']); ?></td>
            <td><?php echo e($data['summary']['payable_days']); ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Employee Name: <?php echo e($data['employee']->name); ?></td>
            <?php for($i = 0; $i <= count($days) + 8; $i++): ?>
                <td></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="font-weight: bold;">Days</td>
            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td style="font-weight: bold;"><?php echo e($day['day']); ?> <?php echo e($day['day_name']); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php for($i = 0; $i < 8; $i++): ?>
                <td></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="font-weight: bold;">Status</td>
            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><?php echo e($data['dailyData'][$day['date']]['status']); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php for($i = 0; $i < 8; $i++): ?>
                <td></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="font-weight: bold;">InTime</td>
            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><?php echo e($data['dailyData'][$day['date']]['inTime'] !== '00:00' ? $data['dailyData'][$day['date']]['inTime'] : ''); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php for($i = 0; $i < 8; $i++): ?>
                <td></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="font-weight: bold;">OutTime</td>
            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><?php echo e($data['dailyData'][$day['date']]['outTime'] !== '00:00' ? $data['dailyData'][$day['date']]['outTime'] : ''); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php for($i = 0; $i < 8; $i++): ?>
                <td></td>
            <?php endfor; ?>
        </tr>
        <tr>
            <td style="font-weight: bold;">Total</td>
            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><?php echo e($data['dailyData'][$day['date']]['totalTime'] !== '00:00' ? $data['dailyData'][$day['date']]['totalTime'] : ''); ?></td>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php for($i = 0; $i < 8; $i++): ?>
                <td></td>
            <?php endfor; ?>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table>
<?php /**PATH C:\xampp\htdocs\hrm_archivista\resources\views/attendance/export.blade.php ENDPATH**/ ?>