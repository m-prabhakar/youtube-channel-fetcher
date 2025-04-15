<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YouTube Channel Fetcher</title>
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen py-8 px-4">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow-md" x-data="{
        fields: {
            channel_name: true,
            video_title: true,
            published_date: true,
            views: true,
            likes: true,
            dislikes: true,
            comments: true,
            video_id: true,
            duration: true, 
            tags: true 
        }
    }">
        <h1 class="text-2xl font-bold mb-4 text-gray-800">YouTube Channel Fetcher</h1>

        <form method="POST" action="{{ route('fetch.channels') }}" target="_blank" id="channel-form">
            @csrf

            <!-- Textarea for URLs -->
            <label for="url-box" class="block text-sm font-medium text-gray-700 mb-1">YouTube Video URLs</label>
            <textarea name="urls" rows="6" id="url-box"
                class="w-full border border-gray-300 rounded p-3 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-6"
                placeholder="One YouTube URL per line...">{{ old('urls') }}</textarea>

            <!-- Toggles -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-6">
                <template x-for="(label, key) in {
                    channel_name: 'Channel Name',
                    video_title: 'Video Title',
                    published_date: 'Publish Date',
                    views: 'Views',
                    likes: 'Likes',
                    dislikes: 'Dislikes',
                    comments: 'Comments',
                    video_id: 'Video ID',
                    duration: 'Duration',
                    tags: 'Tags',
                }" :key="key">
                    <div class="flex items-center justify-between bg-gray-100 px-3 py-2 rounded shadow-sm text-sm">
                        <span x-text="label" class="text-gray-700"></span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" :name="'fields[]'" :value="key" x-model="fields[key]" class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-300 peer-checked:bg-blue-600 rounded-full transition peer-focus:ring-2 peer-focus:ring-blue-300">
                                <div class="absolute w-4 h-4 bg-white rounded-full left-0.5 top-0.5 peer-checked:translate-x-4 transition-transform"></div>
                            </div>
                        </label>
                    </div>
                </template>
            </div>

            <!-- Buttons -->
            <div class="flex flex-wrap gap-4">
                <button type="submit" name="action" value="view"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
                    View
                </button>

                <button type="submit" formaction="{{ route('export.channels') }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                    Export
                </button>

                <button type="button" onclick="document.getElementById('url-box').value = ''"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                    Refresh
                </button>
            </div>
        </form>
    </div>
</body>
</html>
