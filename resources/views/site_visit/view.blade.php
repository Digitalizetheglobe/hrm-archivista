<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label font-bold">{{ __('Employee') }}</label>
                <p>{{ !empty($siteVisit->employee) ? $siteVisit->employee->name : '' }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label font-bold">{{ __('Date') }}</label>
                <p>{{ Auth::user()->dateFormat($siteVisit->date) }}</p>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label font-bold">{{ __('Location') }}</label>
                <p>{{ $siteVisit->location }}</p>
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label class="form-label font-bold">{{ __('Reason') }}</label>
                <p>{{ $siteVisit->reason ?? __('N/A') }}</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label font-bold">{{ __('Status') }}</label>
                <p>
                    @if($siteVisit->status == 'Pending')
                        <span class="badge bg-warning p-2 px-3 rounded">{{ __($siteVisit->status) }}</span>
                    @elseif($siteVisit->status == 'Approved')
                        <span class="badge bg-success p-2 px-3 rounded">{{ __($siteVisit->status) }}</span>
                    @else
                        <span class="badge bg-danger p-2 px-3 rounded">{{ __($siteVisit->status) }}</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    @if(Auth::user()->type != 'employee' && $siteVisit->status == 'Pending')
        {!! Form::open(['method' => 'POST', 'route' => ['site-visit.approve', $siteVisit->id]]) !!}
            <input type="submit" value="{{ __('Approve') }}" class="btn btn-success">
        {!! Form::close() !!}
        {!! Form::open(['method' => 'POST', 'route' => ['site-visit.reject', $siteVisit->id]]) !!}
            <input type="submit" value="{{ __('Reject') }}" class="btn btn-danger">
        {!! Form::close() !!}
    @endif
    <input type="button" value="{{ __('Close') }}" class="btn btn-light" data-bs-dismiss="modal">
</div>
