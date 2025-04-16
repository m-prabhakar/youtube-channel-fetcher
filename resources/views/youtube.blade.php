<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YouTube Channel Fetcher</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body 
    class="bg-gray-100 min-h-screen py-8 px-4"
    data-fetch-url="{{ route('fetch.channels') }}"
    data-export-url="{{ route('export.channels') }}"
>
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow-md" 
         x-data="{
            fields: {
                channel_name: true,
                video_title: true,
                published_date: true,
                views: true,
                likes: true,
                comments: true,
                video_id: true,
                duration: true,
                tags: true
            }
        }">

        <h1 class="text-2xl font-bold mb-4 text-gray-800">YouTube Channel Fetcher</h1>

        <form method="POST" action="{{ route('fetch.channels') }}" id="channel-form">
            @csrf

            <!-- URL Input -->
            <label for="url-box" class="block text-sm font-medium text-gray-700 mb-1">YouTube Video URLs</label>
            <textarea name="urls" rows="6" id="url-box"
                      class="w-full border border-gray-300 rounded p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6"
                      placeholder="One YouTube URL per line...">{{ old('urls') }}</textarea>

            <!-- Field Toggles -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-6">
                <template x-for="(label, key) in {
                    channel_name: 'Channel Name',
                    video_title: 'Video Title',
                    published_date: 'Publish Date',
                    views: 'Views',
                    likes: 'Likes',
                    comments: 'Comments',
                    video_id: 'Video ID',
                    duration: 'Duration',
                    tags: 'Tags'
                }" :key="key">
                    <div class="flex items-center justify-between bg-gray-100 px-3 py-2 rounded shadow-sm text-sm">
                        <span x-text="label" class="text-gray-700"></span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" :name="'fields[]'" :value="key" x-model="fields[key]" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-300 peer-checked:bg-blue-600 rounded-full transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white border border-gray-300 rounded-full transition-transform duration-300 transform peer-checked:translate-x-4"></div>
                        </label>
                    </div>
                </template>
            </div>

            <!-- Buttons -->
            <div class="flex flex-wrap gap-4">
                <button type="button" id="view-button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                    View
                </button>
                <button type="button" id="export-button" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                    Export
                </button>
                <button type="button" onclick="clearForm()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                    Refresh
                </button>
            </div>
        </form>

        <!-- AJAX error message box -->
        <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 mt-4 rounded"></div>

       


        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 mt-4 rounded">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <style>
        #error-message {
            opacity: 0;
            transform: translateY(10px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        #error-message.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    <script>
        function clearForm() {
            document.getElementById('url-box').value = '';
            const errorBox = document.getElementById('error-message');
            errorBox.textContent = '';
            errorBox.classList.add('hidden');
        }

        function showError(message) {
        const errorBox = document.getElementById('error-message');
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');

        // Trigger the transition
        requestAnimationFrame(() => {
            errorBox.classList.add('show');
        });

        // Hide after 4s with transition
        setTimeout(() => {
            errorBox.classList.remove('show');

            // Wait for fade-out transition before hiding completely
            setTimeout(() => {
                errorBox.classList.add('hidden');
                errorBox.textContent = '';
            }, 500); // match the transition duration
        }, 4000);
    }

        const fetchUrl = document.body.getAttribute('data-fetch-url');
        const exportUrl = document.body.getAttribute('data-export-url');

        document.getElementById('view-button').addEventListener('click', () => handleSubmit('view'));
        document.getElementById('export-button').addEventListener('click', () => handleSubmit('export'));

        async function handleSubmit(action) {
            const textarea = document.getElementById('url-box');
            const errorBox = document.getElementById('error-message');
            const form = document.getElementById('channel-form');
            const csrf = form.querySelector('input[name=_token]').value;
            const urls = textarea.value.trim();
            const selectedFields = [...form.querySelectorAll('input[type=checkbox]:checked')].map(cb => cb.value);

            errorBox.classList.add('hidden');
            errorBox.textContent = '';

            if (!urls) {
                showError('Please enter at least one YouTube URL.');
                return;
            }

            if (selectedFields.length === 0) {
                showError('Please select at least one field to fetch.');
                return;
            }

            const route = action === 'view' ? fetchUrl : exportUrl;

            try {
                const response = await fetch(route, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ urls, fields: selectedFields }),
                });

                if (!response.ok) {
                    const result = await response.json();
                    showError(result?.error || result?.message || 'Something went wrong.');
                    return;
                }

                if (action === 'view') {
                    const html = await response.text();
                    const width = 1000;
                    const height = 700;
                    const left = (window.screen.width / 2) - (width / 2);
                    const top = (window.screen.height / 2) - (height / 2);
                    const popup = window.open('', '_blank', `width=${width},height=${height},left=${left},top=${top}`);

                    if (popup) {
                        popup.document.open();
                        popup.document.write(html);
                        popup.document.close();
                    } else {
                        showError('Popup blocked. Please allow popups for this site.');
                    }
                } else {
                    const blob = await response.blob();
                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = 'youtube_export.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                }
            } catch (err) {
                showError(err.message || 'An error occurred.');
            }
        }
    </script>
</body>
</html>
