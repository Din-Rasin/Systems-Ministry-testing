@extends('layouts.app')

@section('content')
	<h1 class="text-xl font-semibold mb-4">Admin: Users</h1>
	<section class="bg-white p-4 rounded shadow mb-6">
		<h2 class="font-semibold mb-2">Create User</h2>
		<form method="post" action="{{ route('admin.users.store') }}" class="grid gap-3">
			@csrf
			<div class="grid grid-cols-3 gap-3">
				<input name="name" placeholder="Name" class="border rounded p-2" />
				<input name="email" placeholder="Email" class="border rounded p-2" />
				<input name="password" placeholder="Password" class="border rounded p-2" type="password" />
			</div>
			<div class="grid grid-cols-2 gap-3">
				<label>
					<span class="block text-sm">Departments</span>
					<select multiple name="department_ids[]" class="border rounded p-2 w-full h-32">
						@foreach($departments as $d)
							<option value="{{ $d->id }}">{{ $d->name }}</option>
						@endforeach
					</select>
				</label>
				<label>
					<span class="block text-sm">Roles</span>
					<select multiple name="role_ids[]" class="border rounded p-2 w-full h-32">
						@foreach($roles as $r)
							<option value="{{ $r->id }}">{{ $r->name }} ({{ $r->slug }})</option>
						@endforeach
					</select>
				</label>
			</div>
			<button class="bg-blue-600 text-white px-4 py-2 rounded">Create</button>
		</form>
	</section>
	<section class="bg-white p-4 rounded shadow">
		<h2 class="font-semibold mb-2">Users</h2>
		<table class="w-full">
			<thead class="bg-gray-100 text-left">
				<tr>
					<th class="p-2">Name</th>
					<th class="p-2">Email</th>
					<th class="p-2">Departments</th>
					<th class="p-2">Roles</th>
				</tr>
			</thead>
			<tbody>
				@foreach($users as $u)
					<tr class="border-t">
						<td class="p-2">{{ $u->name }}</td>
						<td class="p-2">{{ $u->email }}</td>
						<td class="p-2">@foreach($u->departments as $d)<span class="px-2 py-1 bg-gray-100 rounded mr-1">{{ $d->name }}</span>@endforeach</td>
						<td class="p-2">@foreach($u->roles as $r)<span class="px-2 py-1 bg-gray-100 rounded mr-1">{{ $r->name }}</span>@endforeach</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</section>
@endsection