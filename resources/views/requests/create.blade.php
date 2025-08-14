@extends('layouts.app')

@section('content')
	<h1 class="text-xl font-semibold mb-4">New Request</h1>
	<form method="post" action="{{ route('requests.store') }}" class="bg-white p-4 rounded shadow grid gap-3">
		@csrf
		<label class="block">
			<span class="block text-sm">Type</span>
			<select name="type" class="border rounded p-2 w-full">
				<option value="leave">Leave</option>
				<option value="mission">Mission</option>
			</select>
		</label>
		<label class="block">
			<span class="block text-sm">Department</span>
			<select name="department_id" class="border rounded p-2 w-full">
				@foreach($departments as $dep)
					<option value="{{ $dep->id }}">{{ $dep->name }}</option>
				@endforeach
			</select>
		</label>
		<div class="grid grid-cols-2 gap-3">
			<label class="block">
				<span class="block text-sm">Start date (leave)</span>
				<input type="date" name="start_date" class="border rounded p-2 w-full" />
			</label>
			<label class="block">
				<span class="block text-sm">End date (leave)</span>
				<input type="date" name="end_date" class="border rounded p-2 w-full" />
			</label>
		</div>
		<label class="block">
			<span class="block text-sm">Destination (mission)</span>
			<input type="text" name="destination" class="border rounded p-2 w-full" />
		</label>
		<label class="block">
			<span class="block text-sm">Reason</span>
			<textarea name="reason" class="border rounded p-2 w-full" rows="3"></textarea>
		</label>
		<button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Submit</button>
	</form>
@endsection