<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Workflow Demo</title>
		@vite(['resources/css/app.css','resources/js/app.js'])
	</head>
	<body class="bg-gray-50 text-gray-900">
		<nav class="bg-white border-b">
			<div class="max-w-5xl mx-auto px-4 py-3 flex gap-4 items-center">
				<a href="/" class="font-semibold">Home</a>
				<a href="{{ route('requests.index') }}">My Requests</a>
				<a href="{{ route('requests.create') }}">New Request</a>
				<a href="{{ route('approvals.inbox') }}">Approvals Inbox</a>
				<a href="{{ route('admin.workflows.index') }}">Admin Workflows</a>
				<a href="{{ route('admin.users.index') }}">Admin Users</a>
			</div>
		</nav>
		<main class="max-w-5xl mx-auto p-4">
			@if(session('status'))
				<div class="p-3 bg-green-100 text-green-800 rounded mb-4">{{ session('status') }}</div>
			@endif
			@yield('content')
		</main>
	</body>
</html>