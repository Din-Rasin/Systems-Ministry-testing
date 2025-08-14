@extends('layouts.app')

@section('content')
	<h1 class="text-xl font-semibold mb-4">My Requests</h1>
	<table class="w-full bg-white rounded shadow overflow-hidden">
		<thead class="bg-gray-100 text-left">
			<tr>
				<th class="p-2">#</th>
				<th class="p-2">Type</th>
				<th class="p-2">Department</th>
				<th class="p-2">Period/Destination</th>
				<th class="p-2">Status</th>
			</tr>
		</thead>
		<tbody>
			@forelse($requests as $req)
				<tr class="border-t">
					<td class="p-2">{{ $req->id }}</td>
					<td class="p-2 capitalize">{{ $req->type }}</td>
					<td class="p-2">{{ $req->department->name ?? '-' }}</td>
					<td class="p-2">
						@if($req->type === 'leave')
							{{ $req->start_date }} → {{ $req->end_date }}
						@else
							{{ $req->destination ?? '-' }}
						@endif
					</td>
					<td class="p-2">
						<span class="px-2 py-1 rounded text-sm {{ $req->status === 'approved' ? 'bg-green-100 text-green-800' : ($req->status==='rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
							{{ ucfirst($req->status) }}
						</span>
					</td>
				</tr>
			@empty
				<tr><td colspan="5" class="p-4 text-center text-gray-500">No requests yet.</td></tr>
			@endforelse
		</tbody>
	</table>
	<div class="mt-4">{{ $requests->links() }}</div>
@endsection