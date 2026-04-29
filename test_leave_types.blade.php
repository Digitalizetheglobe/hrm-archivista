@extends('layouts.app')

@section('content')
<div style="padding: 20px;">
    <h2>Debug Leave Types</h2>
    
    <?php
    $leavetypes = \App\Models\LeaveType::where('created_by', '=', \Auth::user()->creatorId())->get();
    
    foreach ($leavetypes as $leave) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<strong>Title:</strong> " . $leave->title . "<br>";
        echo "<strong>Days:</strong> " . $leave->days . "<br>";
        echo "<strong>Type:</strong> " . $leave->type . "<br>";
        echo "<strong>Is Unlimited:</strong> " . ($leave->is_unlimited ? 'YES' : 'NO') . "<br>";
        echo "</div>";
    }
    ?>
    
    <hr>
    
    <h3>Template Test</h3>
    <select>
        <option value="">Select Leave Type</option>
        @foreach ($leavetypes as $leave)
            <option value="{{ $leave->id }}">
                {{ $leave->title }} 
                @if($leave->is_unlimited)
                    ({{ __('Unlimited') }})
                @else
                    ({{ $leave->days }} {{ __('Days') }})
                @endif
            </option>
        @endforeach
    </select>
</div>
@endsection
