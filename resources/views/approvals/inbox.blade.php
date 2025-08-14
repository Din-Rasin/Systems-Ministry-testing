@extends('layouts.app')

@section('content')
	<h1 class="text-xl font-semibold mb-4">Approvals Inbox</h1>
	<table class="w-full bg-white rounded shadow overflow-hidden">
		<thead class="bg-gray-100 text-left">
			<tr>
				<th class="p-2">Req #</th>
				<th class="p-2">Type</th>
				<th class="p-2">Department</th>
				<th class="p-2">Requester</th>
				<th class="p-2">Step</th>
				<th class="p-2">Action</th>
			</tr>
		</thead>
		<tbody>
			@forelse($pendingApprovals as $ap)
				<tr class="border-t">
					<td class="p-2">{{ $ap->request->id }}</td>
					<td class="p-2 capitalize">{{ $ap->request->type }}</td>
					<td class="p-2">{{ $ap->request->department->name ?? '-' }}</td>
					<td class="p-2">{{ $ap->request->user->name }}</td>
					<td class="p-2">Approver: {{ strtoupper($ap->step->approver_role_slug) }}</td>
					<td class="p-2">
						<form method="post" action="{{ route('approvals.decide', $ap) }}" class="inline">
							@csrf
							<input type="hidden" name="decision" value="approved" />
							<button class="bg-green-600 text-white px-3 py-1 rounded">Approve</button>
						</form>
						<form method="post" action="{{ route('approvals.decide', $ap) }}" class="inline ml-2">
							@csrf
							<input type="hidden" name="decision" value="rejected" />
							<button class="bg-red-600 text-white px-3 py-1 rounded">Reject</button>
						</form>
					</td>
				</tr>
			@empty
				<tr><td colspan="6" class="p-4 text-center text-gray-500">No items.</td></tr>
			@endforelse
		</tbody>
	</table>
	<div class="mt-4">{{ $pendingApprovals->links() }}</div>
@endsection