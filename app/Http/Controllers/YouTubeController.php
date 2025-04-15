<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ChannelExport;

class YouTubeController extends Controller
{
    public function index()
    {
        return view('youtube');
    }

    public function fetchChannels(Request $request)
    {
        set_time_limit(0);

        $urls = array_filter(array_map('trim', explode("\n", $request->input('urls'))));
        $fields = $request->input('fields', []);
        $results = [];

        $videoIds = [];
        foreach ($urls as $url) {
            $videoId = $this->extractVideoId($url);
            if ($videoId) {
                $videoIds[$videoId] = $url;
            }
        }

        $chunks = array_chunk(array_keys($videoIds), 50);

        foreach ($chunks as $chunk) {
            $videosData = $this->getVideosData($chunk);

            foreach ($chunk as $videoId) {
                $videoData = $videosData[$videoId] ?? null;
                if (!$videoData) continue;

                $row = ['Video URL' => $videoIds[$videoId]];

                if (in_array('channel_name', $fields)) {
                    $row['Channel Name'] = $videoData['channelTitle'] ?? 'N/A';
                }
                if (in_array('video_title', $fields)) {
                    $row['Video Title'] = $videoData['title'] ?? 'N/A';
                }
                if (in_array('published_date', $fields)) {
                    $row['Published Date'] = $videoData['publishedAt'] ?? 'N/A';
                }
                if (in_array('views', $fields)) {
                    $row['Views'] = $videoData['viewCount'] ?? 'N/A';
                }
                if (in_array('likes', $fields)) {
                    $row['Likes'] = $videoData['likeCount'] ?? 'N/A';
                }
                if (in_array('dislikes', $fields)) {
                    $row['Dislikes'] = 'N/A'; // Not provided by API
                }
                if (in_array('comments', $fields)) {
                    $row['Comments'] = $videoData['commentCount'] ?? 'N/A';
                }
                if (in_array('video_id', $fields)) {
                    $row['Video ID'] = $videoId;
                }
                if (in_array('duration', $fields)) {
                    $row['Duration'] = $videoData['duration'] ?? 'N/A';
                }
                if (in_array('tags', $fields)) {
                    $row['Tags'] = !empty($videoData['tags']) ? implode(', ', $videoData['tags']) : 'N/A';
                }

                $results[] = $row;
            }
        }

        session(['channel_results' => $results, 'selected_fields' => $fields]);

        return view('results', compact('results', 'fields'));
    }

    public function exportChannels(Request $request)
    {
        $results = session('channel_results', []);
        if (empty($results)) {
            return redirect()->route('index')->with('error', 'No data to export.');
        }

        return Excel::download(new ChannelExport($results), 'youtube_channels.xlsx');
    }

    public function results()
    {
        $results = session('channel_results', []);
        return view('results', compact('results'));
    }

    private function extractVideoId($url)
    {
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['host']) && $parsedUrl['host'] === 'youtu.be') {
            return ltrim($parsedUrl['path'], '/');
        }

        parse_str($parsedUrl['query'] ?? '', $query);
        return $query['v'] ?? null;
    }

    private function getVideosData(array $videoIds)
    {
        $apiKey = config('services.youtube.api_key');

        $response = Http::get("https://www.googleapis.com/youtube/v3/videos", [
            'part' => 'snippet,statistics,contentDetails',
            'id' => implode(',', $videoIds),
            'key' => $apiKey
        ]);

        $data = $response->json();
        $videos = [];

        foreach ($data['items'] ?? [] as $item) {
            $id = $item['id'];
            $videos[$id] = [
                'title' => $item['snippet']['title'] ?? 'N/A',
                'channelTitle' => $item['snippet']['channelTitle'] ?? 'N/A',
                'publishedAt' => $item['snippet']['publishedAt'] ?? 'N/A',
                'tags' => $item['snippet']['tags'] ?? [],
                'viewCount' => $item['statistics']['viewCount'] ?? 'N/A',
                'likeCount' => $item['statistics']['likeCount'] ?? 'N/A',
                'commentCount' => $item['statistics']['commentCount'] ?? 'N/A',
                'duration' => $this->convertDurationToReadable($item['contentDetails']['duration'] ?? 'PT0S'),
            ];
        }

        return $videos;
    }

    private function convertDurationToReadable($duration)
    {
        try {
            $interval = new \DateInterval($duration);
            $hours = $interval->h + ($interval->d * 24);
            $minutes = $interval->i;
            $seconds = $interval->s;

            if ($hours > 0) {
                return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            }

            return sprintf('%02d:%02d', $minutes, $seconds);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
}
