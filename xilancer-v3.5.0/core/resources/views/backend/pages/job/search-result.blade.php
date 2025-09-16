<x-validation.error />
<table class="DataTable_activation">
    <thead>
    <tr>
        <th class="no-sort">
            <div class="mark-all-checkbox">
                <input type="checkbox" class="all-checkbox">
            </div>
        </th>
        <th>{{__('ID')}}</th>
        <th>{{__('Job Title')}}</th>
        <th>{{__('Status (change by admin)')}}</th>
        <th>{{__('Action')}}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($all_jobs as $job)
        @php
             $fake_badge = $job->is_fake == 1 ? '&nbsp;<span class="badge bg-danger">'.__('Fake').'</span>' : '';
        @endphp
        <tr>
            <td> <x-bulk-action.bulk-delete-checkbox :id="$job->id"/> </td>
            <td>{{ $job->id }}</td>
            <td>
                {{ $job->title }} {!! $fake_badge !!}<br>
            </td>
            <td>
                <x-status.table.active-inactive :status="$job->status"/>
            </td>
            <td>
                <x-status.table.select-action :title="__('Select Action')"/>
                <ul class="dropdown-menu status_dropdown__list">
                    @can('job-details')
                    <li class="status_dropdown__item">
                        <a href="{{ route('admin.job.details',$job->id) }}" class="btn dropdown-item status_dropdown__list__link">{{ __('Job Details') }}</a>
                    </li>
                    @endcan
                    @can('job-delete')
                    <li class="status_dropdown__item">
                        <x-popup.delete-popup :title="__('Delete Job')" :url="route('admin.job.delete',$job->id)"/>
                    </li>
                    @endcan
                    @can('job-status-change')
                    <li class="status_dropdown__item">
                        @if($job->status === 0)
                            <x-status.table.status-change :title="__('Approve Job')" :url="route('admin.job.status.change',$job->id)"/>
                        @else
                            <x-status.table.status-change :title="__('Inactivate Job')" :url="route('admin.job.status.change',$job->id)"/>
                        @endif
                    </li>
                    @endcan
                </ul>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<x-pagination.laravel-paginate :allData="$all_jobs"/>
