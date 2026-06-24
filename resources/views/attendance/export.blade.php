<table>
    <tr></tr>
    <tr>
        <td style="font-weight: bold;">{{ $start_date }} To {{ $end_date }}</td>
        @for($i = 0; $i < count($days); $i++)
            <td></td>
        @endfor
        <td colspan="8" style="font-weight: bold; text-align: center;">Summary</td>
    </tr>
    <tr>
        @for($i = 0; $i <= count($days); $i++)
            <td></td>
        @endfor
        <td style="font-weight: bold;">Monthly Days</td>
        <td style="font-weight: bold;">Total Present Days</td>
        <td style="font-weight: bold;">Early Leaving</td>
        <td style="font-weight: bold;">Half Day</td>
        <td style="font-weight: bold;">Total LWP</td>
        <td style="font-weight: bold;">Week Off</td>
        <td style="font-weight: bold;">Total Leave</td>
        <td style="font-weight: bold;">Total Payable Days</td>
    </tr>

    @foreach($reportData as $data)
        <tr>
            <td style="font-weight: bold;">Employee Code: {{ $data['employee']->id }}</td>
            @for($i = 0; $i < count($days); $i++)
                <td></td>
            @endfor
            <td>{{ $data['summary']['monthly_days'] }}</td>
            <td>{{ $data['summary']['present'] }}</td>
            <td>{{ $data['summary']['early_leaving'] }}</td>
            <td>{{ $data['summary']['half_day'] }}</td>
            <td>{{ $data['summary']['lwp'] }}</td>
            <td>{{ $data['summary']['week_off'] }}</td>
            <td>{{ $data['summary']['leave'] }}</td>
            <td>{{ $data['summary']['payable_days'] }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Employee Name: {{ $data['employee']->name }}</td>
            @for($i = 0; $i <= count($days) + 8; $i++)
                <td></td>
            @endfor
        </tr>
        <tr>
            <td style="font-weight: bold;">Days</td>
            @foreach($days as $day)
                <td style="font-weight: bold;">{{ $day['day'] }} {{ $day['day_name'] }}</td>
            @endforeach
            @for($i = 0; $i < 8; $i++)
                <td></td>
            @endfor
        </tr>
        <tr>
            <td style="font-weight: bold;">Status</td>
            @foreach($days as $day)
                <td>{{ $data['dailyData'][$day['date']]['status'] }}</td>
            @endforeach
            @for($i = 0; $i < 8; $i++)
                <td></td>
            @endfor
        </tr>
        <tr>
            <td style="font-weight: bold;">InTime</td>
            @foreach($days as $day)
                <td>{{ $data['dailyData'][$day['date']]['inTime'] !== '00:00' ? $data['dailyData'][$day['date']]['inTime'] : '' }}</td>
            @endforeach
            @for($i = 0; $i < 8; $i++)
                <td></td>
            @endfor
        </tr>
        <tr>
            <td style="font-weight: bold;">OutTime</td>
            @foreach($days as $day)
                <td>{{ $data['dailyData'][$day['date']]['outTime'] !== '00:00' ? $data['dailyData'][$day['date']]['outTime'] : '' }}</td>
            @endforeach
            @for($i = 0; $i < 8; $i++)
                <td></td>
            @endfor
        </tr>
        <tr>
            <td style="font-weight: bold;">Total</td>
            @foreach($days as $day)
                <td>{{ $data['dailyData'][$day['date']]['totalTime'] !== '00:00' ? $data['dailyData'][$day['date']]['totalTime'] : '' }}</td>
            @endforeach
            @for($i = 0; $i < 8; $i++)
                <td></td>
            @endfor
        </tr>
    @endforeach
</table>
