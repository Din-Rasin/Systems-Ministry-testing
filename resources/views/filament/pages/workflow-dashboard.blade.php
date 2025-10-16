<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Laravel Workflow Management System
                </h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p>
                        This system demonstrates a comprehensive workflow management solution for leave and mission requests
                        as specified in the assignment requirements.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h4 class="text-md font-medium text-gray-900 mb-3">System Features</h4>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>User management with role-based access control</li>
                        <li>Department-specific workflow configurations</li>
                        <li>Leave request submission and approval workflow</li>
                        <li>Mission request submission and approval workflow</li>
                        <li>Multi-step approval processes</li>
                        <li>Real-time status tracking</li>
                    </ul>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h4 class="text-md font-medium text-gray-900 mb-3">Workflow Examples</h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <div>
                            <strong>IT Department Leave:</strong>
                            <br>Team Leader → HR Manager
                        </div>
                        <div>
                            <strong>IT Department Mission:</strong>
                            <br>Team Leader → CEO
                        </div>
                        <div>
                            <strong>Sales Department Leave:</strong>
                            <br>Team Leader → CFO → HR Manager
                        </div>
                        <div>
                            <strong>Sales Department Mission:</strong>
                            <br>Team Leader → CFO → HR Manager → CEO
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Admin Login Credentials
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p><strong>Email:</strong> admin@gmail.com</p>
                        <p><strong>Password:</strong> 123</p>
                        <p><strong>URL:</strong> <a href="http://127.0.0.1:8888/admin/login" class="underline">http://127.0.0.1:8888/admin/login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>