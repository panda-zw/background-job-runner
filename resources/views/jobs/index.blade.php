<x-app-layout>
    <x-slot name="header">
        <div class="flex align-middle">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Jobs') }}
            </h2>
            <!-- Background Runner Status -->
            <div x-data="{ isRunning: false }" x-init="(async () => {
                const response = await fetch('{{ route('background.runner.status') }}');
                const data = await response.json();
                isRunning = data.isRunning;
            })();
            setInterval(async () => {
                const response = await fetch('{{ route('background.runner.status') }}');
                const data = await response.json();
                isRunning = data.isRunning;
            }, 5000);" class="ml-auto">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mb-4">
                    Background Runner Status:
                    <span class="ml-2">
                        <span x-show="isRunning" class="h-4 w-4 bg-green-500 rounded-full inline-block"></span>
                        <span x-show="!isRunning" class="h-4 w-4 bg-red-500 rounded-full inline-block"></span>
                    </span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Job Submission Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form action="{{ route('jobs.run') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="mb-2">
                            <label for="job_class" class="block text-gray-700 font-bold mb-2">Select Job Class</label>
                            <select name="job_class" id="job_class"
                                class="form-select border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-full">
                                <option value="" disabled selected>Select a Job Class</option>
                                @foreach ($jobClasses as $job)
                                    <option value="{{ $job['class'] }}">{{ $job['title'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label for="priority" class="block text-gray-700 font-bold mb-2">Priority</label>
                            <input type="number" name="priority" id="priority" min="0"
                                class="form-input border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-full"
                                value="0" required>
                        </div>

                        <div class="mb-4">
                            <label for="retry_count" class="block text-gray-700 font-bold mb-2">Retry Count</label>
                            <input type="number" name="retry_count" id="retry_count" min="0"
                                class="form-input border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-full"
                                value="0" required>
                        </div>

                        <div class="mb-4">
                            <label for="delay" class="block text-gray-700 font-bold mb-2">Delay (in seconds)</label>
                            <input type="number" name="delay" id="delay" min="0"
                                class="form-input border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-full"
                                value="0" required>
                        </div>
                    </div>

                    <div>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Submit Job
                        </button>
                    </div>
                </form>
            </div>

            <!-- Existing Jobs Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="table-auto w-full text-start border-collapse border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border border-gray-300 p-2">ID</th>
                            <th class="border border-gray-300 p-2">Class</th>
                            <th class="border border-gray-300 p-2">Method</th>
                            <th class="border border-gray-300 p-2">Status</th>
                            <th class="border border-gray-300 p-2">Priority</th>
                            <th class="border border-gray-300 p-2">Retry Count</th>
                            <th class="border border-gray-300 p-2">Available At</th>
                            <th class="border border-gray-300 p-2">Execution Time</th>
                            <th class="border border-gray-300 p-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jobs as $job)
                            <tr>
                                <td class="border border-gray-300 p-2">{{ $job->id }}</td>
                                <td class="border border-gray-300 p-2">{{ $job->job_class }}</td>
                                <td class="border border-gray-300 p-2">{{ $job->method }}</td>
                                <td class="border border-gray-300 p-2">
                                    @if ($job->status == 'pending')
                                        <span class="bg-yellow-100 py-0.5 px-2 rounded text-sm text-yellow-600">{{ ucfirst($job->status) }}</span>
                                    @elseif ($job->status == 'running')
                                        <span class="bg-blue-100 py-0.5 px-2 rounded text-sm text-blue-600">{{ ucfirst($job->status) }}</span>
                                    @elseif ($job->status == 'completed')
                                        <span class="bg-green-100 py-0.5 px-2 rounded text-sm text-green-600">{{ ucfirst($job->status) }}</span>
                                    @elseif ($job->status == 'failed')
                                        <span class="bg-red-100 py-0.5 px-2 rounded text-sm text-red-600">{{ ucfirst($job->status) }}</span>
                                    @endif
                                </td>
                                <td class="border border-gray-300 p-2">{{ $job->priority }}</td>
                                <td class="border border-gray-300 p-2">{{ $job->retry_count }}</td>
                                <td class="border border-gray-300 p-2">
                                    {{ $job->available_at ? $job->available_at->format('Y-m-d H:i:s') : 'Immediately' }}
                                </td>
                                <td class="border border-gray-300 p-2">
                                    {{ $job->updated_at ? $job->updated_at->format('Y-m-d H:i:s') : 'N/A' }}
                                </td>
                                <td class="border border-gray-300 p-2">
                                    @if ($job->status === 'running')
                                        <form action="{{ route('jobs.cancel', $job->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger">Cancel</button>
                                        </form>
                                    @else
                                        <span>N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center p-2">No jobs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
