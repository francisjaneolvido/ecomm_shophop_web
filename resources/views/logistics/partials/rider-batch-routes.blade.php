@forelse($batchData->groupBy('branch_id') as $branchId=>$rows)
    <section class="space-y-3"><h2 class="font-semibold text-navy">{{ $rows->first()['branch'] }}</h2>
        @foreach($rows as $batchInfo)@include('logistics.partials.rider-batch-card')@endforeach
    </section>
@empty<div class="ops-panel p-5 text-sm text-slate-500">No batches match the selected filters.</div>@endforelse
{{ $batches->links() }}
