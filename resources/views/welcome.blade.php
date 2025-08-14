<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Workflow Demo</title>
		@vite(['resources/css/app.css','resources/js/app.js'])
	</head>
	<body class="bg-gray-50 text-gray-900">
		<div class="max-w-3xl mx-auto p-8">
			<h1 class="text-2xl font-bold mb-6">Workflow Demo</h1>
			<div class="grid gap-3">
				<a class="text-blue-600 underline" href="{{ route('requests.create') }}">Submit Leave/Mission Request</a>
				<a class="text-blue-600 underline" href="{{ route('requests.index') }}">View My Requests</a>
				<a class="text-blue-600 underline" href="{{ route('approvals.inbox') }}">Approvals Inbox</a>
				<a class="text-blue-600 underline" href="{{ route('admin.workflows.index') }}">Admin: Manage Workflows</a>
			</div>
			<p class="mt-6 text-gray-600">Tip: use <a class="text-blue-600 underline" href="{{ route('autologin') }}">Auto-Login as Admin</a> to demo quickly.</p>
		</div>
	</body>
</html>
