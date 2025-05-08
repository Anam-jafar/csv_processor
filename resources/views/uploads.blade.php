<!DOCTYPE html>
<html>
<head>
    <title>CSV File Manager</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans min-h-screen py-10">
    <div class="container mx-auto px-4 max-w-5xl">
        <!-- Header Section -->
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">CSV File Manager</h1>
            <p class="text-gray-500">Upload and manage your CSV files in one place</p>
        </div>
        
        <!-- Upload Form -->
        <div class="mb-10 bg-white p-8 rounded-2xl shadow-sm transition-all hover:shadow-md">
            <h2 class="text-xl font-semibold text-gray-700 mb-6">Upload New File</h2>
            <form method="POST" action="{{ route('upload') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    <div class="w-full relative">
                        <input type="file" name="csv_file" accept=".csv" required 
                               class="w-full border border-gray-200 p-3 rounded-lg text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <button type="submit" class="w-full sm:w-auto  min-w-[200px] bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Upload CSV
                    </button>
                </div>
            </form>
        </div>

        <!-- Files Table -->
        <div class="bg-white p-8 rounded-2xl shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold text-gray-700">Upload History</h2>
                <span class="text-sm text-gray-400" id="file-count">0 files</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full table-auto text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-100">
                            <th class="px-4 py-3 font-medium">File Name</th>
                            <th class="px-4 py-3 font-medium">Time</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody id="upload-table-body" class="divide-y divide-gray-50">
                        @foreach($uploads as $upload)
                        <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                            <td class="px-4 py-4 text-gray-800">{{ $upload->original_name }}</td>
                            <td class="px-4 py-4 text-gray-600" data-time="{{ $upload->created_at }}">
                                {{ $upload->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="status-badge">{{ $upload->status }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Empty state -->
            <div id="empty-state" class="hidden py-16 text-center">
                <div class="bg-gray-50 rounded-xl p-8 max-w-md mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 mb-2">No files uploaded yet</p>
                    <p class="text-gray-400 text-sm">Upload a CSV file to get started</p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-gray-400 text-sm">
            <p>Upload CSV files for processing. Supported format: .csv</p>
        </div>
    </div>

<script>
    function timeAgo(datetime) {
        const now = new Date();
        const then = new Date(datetime);
        const diffMs = now - then;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);

        if (diffMins < 1) return '(just now)';
        if (diffMins < 60) return `(${diffMins} minutes ago)`;
        if (diffHours < 24) return `(${diffHours} hours ago)`;
        return `(${then.toLocaleDateString()} ${then.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true })})`;
    }

    function formatDateAMPM(datetime) {
        const date = new Date(datetime);
        const options = {
            year: 'numeric', month: '2-digit', day: '2-digit',
            hour: '2-digit', minute: '2-digit',
            hour12: true
        };
        return date.toLocaleString('en-US', options);
    }

    function getStatusBadge(status) {
        const base = 'px-2.5 py-1 text-xs rounded-full font-medium inline-flex items-center';
        switch (status.toLowerCase()) {
            case 'completed': 
                return `<span class="${base} bg-green-50 text-green-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                    Completed
                </span>`;
            case 'processing': 
                return `<span class="${base} bg-yellow-50 text-yellow-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1.5 animate-pulse"></span>
                    Processing
                </span>`;
            case 'failed': 
                return `<span class="${base} bg-red-50 text-red-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5"></span>
                    Failed
                </span>`;
            default: 
                return `<span class="${base} bg-gray-50 text-gray-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></span>
                    ${status}
                </span>`;
        }
    }

    async function pollStatus() {
        try {
            const response = await fetch('/api/uploads');
            const json = await response.json();
            const uploads = json.data;
            
            const tbody = document.getElementById('upload-table-body');
            const emptyState = document.getElementById('empty-state');
            const fileCount = document.getElementById('file-count');
            
            // Update file count
            fileCount.textContent = uploads.length === 1 ? '1 file' : `${uploads.length} files`;
            
            if (uploads.length === 0) {
                tbody.innerHTML = '';
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
                tbody.innerHTML = '';

                uploads.forEach(upload => {
                    const createdAtFormatted = formatDateAMPM(upload.created_at);
                    const relativeTime = timeAgo(upload.created_at);

                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50/50 transition-colors duration-150';
                    row.innerHTML = `
                        <td class="px-4 py-4 text-gray-800">${upload.original_name}</td>
                        <td class="px-4 py-4 text-gray-600" title="${upload.created_at}">
                            ${createdAtFormatted} <span class="text-gray-400 text-xs ml-1">${relativeTime}</span>
                        </td>
                        <td class="px-4 py-4">${getStatusBadge(upload.status)}</td>
                    `;
                    tbody.appendChild(row);
                });
            }
        } catch (err) {
            console.error('Polling failed', err);
        }

        setTimeout(pollStatus, 3000); 
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.status-badge').forEach(badge => {
            const status = badge.textContent.trim();
            badge.outerHTML = getStatusBadge(status);
        });
        
        pollStatus();
        
        const tbody = document.getElementById('upload-table-body');
        const emptyState = document.getElementById('empty-state');
        const fileCount = document.getElementById('file-count');
        
        const initialCount = tbody.children.length;
        fileCount.textContent = initialCount === 1 ? '1 file' : `${initialCount} files`;
        
        if (initialCount === 0) {
            emptyState.classList.remove('hidden');
        }
    });
</script>

</body>
</html>