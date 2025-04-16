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
        $urlsRaw = $request->input('urls');
        $fields = $request->input('fields', []);
        $urls = array_filter(array_map('trim', explode("\n", $urlsRaw)));

        if (empty($urlsRaw) || empty(trim($urlsRaw))) {
            return response()->json(['error' => 'Please enter at least one YouTube URL.'], 422);
        }

        if (empty($fields)) {
            return response()->json(['error' => 'Please select at least one field to fetch.'], 422);
        }

        $videoIdMap = [];
        $duplicateUrls = [];
        $invalidUrls = [];

        foreach ($urls as $url) {
            $videoId = $this->extractVideoId($url);
            if (!$videoId) {
                $invalidUrls[] = $url;
                continue;
            }

            if (!isset($videoIdMap[$videoId])) {
                $videoIdMap[$videoId] = [$url];
            } else {
                $videoIdMap[$videoId][] = $url;
                $duplicateUrls[] = $url;
            }
        }

        if (empty($videoIdMap)) {
            return response()->json(['error' => 'Enter a valid YouTube URL.)'], 422);
        }

        $chunks = array_chunk(array_keys($videoIdMap), 50);
        $results = [];
        $missingIds = [];

        foreach ($chunks as $chunk) {
            $videosData = $this->getVideosData($chunk);

            foreach ($chunk as $videoId) {
                if (!isset($videosData[$videoId])) {
                    $missingIds[$videoId] = $videoIdMap[$videoId][0];
                    continue;
                }

                $videoData = $videosData[$videoId];
                $row = ['Video URL' => $videoIdMap[$videoId][0]];

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

        session([
            'channel_results' => $results,
            'selected_fields' => $fields,
            'missing_urls' => $missingIds,
            'invalid_urls' => $invalidUrls,
            'duplicateUrls' => $duplicateUrls,
            'original_input' => $urls,
        ]);

        return view('results', [
            'results' => $results,
            'fields' => $fields,
            'missingUrls' => $missingIds,
            'invalidUrls' => $invalidUrls,
            'duplicateUrls' => $duplicateUrls,
        ]);
    }

    public function exportChannels(Request $request)
    {
        $urlsRaw = $request->input('urls');
        $fields = $request->input('fields', []);
        $urls = array_filter(array_map('trim', explode("\n", $urlsRaw)));

        if (empty($urlsRaw) || empty($urls)) {
            return response()->json(['error' => 'Please enter at least one YouTube URL.'], 422);
        }

        if (empty($fields)) {
            return response()->json(['error' => 'Please select at least one field to export.'], 422);
        }

        $videoIdToUrls = [];
        $invalidUrls = [];
        $duplicateUrls = [];

        foreach ($urls as $url) {
            $videoId = $this->extractVideoId($url);
            if (!$videoId) {
                $invalidUrls[] = $url;
                continue;
            }

            if (!isset($videoIdToUrls[$videoId])) {
                $videoIdToUrls[$videoId] = [$url];
            } else {
                $videoIdToUrls[$videoId][] = $url;
                $duplicateUrls[] = $url;
            }
        }

        if (empty($videoIdToUrls)) {
            return response()->json(['error' => 'Enter valid YouTube URL.'], 422);
        }

        $videoChunks = array_chunk(array_keys($videoIdToUrls), 50);
        $validResults = [];
        $missingVideoIds = [];

        foreach ($videoChunks as $chunk) {
            $videosData = $this->getVideosData($chunk);

            foreach ($chunk as $videoId) {
                if (!isset($videosData[$videoId])) {
                    $missingVideoIds[$videoId] = $videoIdToUrls[$videoId][0];
                    continue;
                }

                $videoData = $videosData[$videoId];
                $row = ['Video URL' => $videoIdToUrls[$videoId][0]];

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

                $validResults[] = $row;
            }
        }

        if (empty($validResults) && empty($missingVideoIds)) {
            return response()->json(['error' => 'Enter valid YouTube URL.'], 422);
        }

        return Excel::download(
            new \App\Exports\YouTubeMultiSheetExport($validResults, $invalidUrls, $missingVideoIds, $duplicateUrls),
            'youtube_export.xlsx'
        );
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
