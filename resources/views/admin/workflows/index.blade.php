@extends('layouts.app')

@section('content')
	<h1 class="text-xl font-semibold mb-4">Admin: Workflows</h1>
	<div class="grid gap-6">
		<section class="bg-white p-4 rounded shadow">
			<h2 class="font-semibold mb-2">Create Workflow</h2>
			<form method="post" action="{{ route('admin.workflows.store') }}" class="grid gap-3">
				@csrf
				<div class="grid grid-cols-2 gap-3">
					<label class="block">
						<span class="block text-sm">Name</span>
						<input name="name" class="border rounded p-2 w-full" />
					</label>
					<label class="block">
						<span class="block text-sm">Request Type</span>
						<select name="request_type" class="border rounded p-2 w-full">
							<option value="leave">Leave</option>
							<option value="mission">Mission</option>
						</select>
					</label>
				</div>
				<label class="block">
					<span class="block text-sm">Department IDs (comma-separated)</span>
					<input name="department_ids_raw" class="border rounded p-2 w-full" placeholder="e.g. 1,2" />
				</label>
				<label class="block">
					<span class="block text-sm">Steps JSON</span>
					<textarea name="steps_raw" rows="4" class="border rounded p-2 w-full" placeholder='[{"approver_role_slug":"team_leader"},{"approver_role_slug":"hr_manager"}]'></textarea>
				</label>
				<button class="bg-blue-600 text-white px-4 py-2 rounded">Create Workflow</button>
			</form>
			<p class="text-sm text-gray-600 mt-2">Use role slugs: team_leader, hr_manager, ceo, cfo.</p>
		</section>

		<section class="bg-white p-4 rounded shadow">
			<h2 class="font-semibold mb-2">Existing Workflows</h2>
			<table class="w-full">
				<thead class="bg-gray-100 text-left">
					<tr>
						<th class="p-2">Name</th>
						<th class="p-2">Type</th>
						<th class="p-2">Steps</th>
					</tr>
				</thead>
				<tbody>
					@foreach($workflows as $wf)
						<tr class="border-t">
							<td class="p-2">{{ $wf->name }}</td>
							<td class="p-2">{{ $wf->request_type }}</td>
							<td class="p-2">@foreach($wf->steps as $s)<span class="px-2 py-1 bg-gray-100 rounded mr-1">{{ $s->approver_role_slug }}</span>@endforeach</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</section>
	</div>

	<script>
	// Convert raw inputs to expected arrays before submit
	document.querySelector('form[action="{{ route('admin.workflows.store') }}"]').addEventListener('submit', function(e){
		const depRaw = this.querySelector('[name="department_ids_raw"]').value.trim();
		const stepsRaw = this.querySelector('[name="steps_raw"]').value.trim();
		if(depRaw){
			const hiddenDeps = document.createElement('input');
			hiddenDeps.type='hidden'; hiddenDeps.name='department_ids[]';
			depRaw.split(',').map(s=>s.trim()).filter(Boolean).forEach((id)=>{
				const inp = hiddenDeps.cloneNode(); inp.value=id; this.appendChild(inp);
			});
		}
		if(stepsRaw){
			try{
				const parsed = JSON.parse(stepsRaw);
				parsed.forEach((s, i)=>{
					const inp = document.createElement('input'); inp.type='hidden';
					inp.name = `steps[${i}][approver_role_slug]`; inp.value = s.approver_role_slug; this.appendChild(inp);
				});
			}catch(err){
				alert('Invalid steps JSON'); e.preventDefault(); return false;
			}
		}
	});
	</script>
@endsection