<?php
/**
 * FLIXFLIX Streaming Engine - Unified PHP Backend API
 * Handles TMDB discovery, VegaMovies search, full post scraping (series/seasons/movies),
 * and high-speed driver mirror resolutions (Cloudflare R2, Google UserContent, HubCloud 10Gbps, Pixeldrain, Gofile, Filepress).
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configuration
define('TMDB_API_KEY', '4e44d9029b1270a757cddc766a1bcb63');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');
define('VEGA_BASE', 'https://new1.vegamovies.lease');

// Determine route / endpoint from query or request URI
$endpoint = $_GET['endpoint'] ?? $_GET['action'] ?? '';
if (empty($endpoint)) {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#/api/(.+)#i', $uri, $m)) {
        $endpoint = $m[1];
    }
}
$endpoint = trim($endpoint, '/');

// Helper: HTTP GET via cURL
function curlGet($url, $referer = VEGA_BASE, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    
    $defaultHeaders = [
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'Referer: ' . $referer,
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9'
    ];
    $mergedHeaders = array_merge($defaultHeaders, $headers);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $mergedHeaders);

    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    return ['status' => $status, 'body' => $body, 'error' => $err];
}

function cleanUrl($url) {
    if (!$url) return null;
    return trim(str_replace('&amp;', '&', $url));
}

// ----------------------------------------------------
// 1. TMDB ENDPOINTS
// ----------------------------------------------------

if ($endpoint === 'tmdb/trending' || $endpoint === 'trending') {
    $time = $_GET['time'] ?? 'day';
    $page = $_GET['page'] ?? 1;
    $url = TMDB_BASE_URL . "/trending/movie/{$time}?api_key=" . TMDB_API_KEY . "&page={$page}";
    $res = curlGet($url);
    echo $res['body'];
    exit;
}

if ($endpoint === 'tmdb/bollywood' || $endpoint === 'bollywood') {
    $page = $_GET['page'] ?? 1;
    $url = TMDB_BASE_URL . "/discover/movie?api_key=" . TMDB_API_KEY . "&with_original_language=hi&sort_by=popularity.desc&page={$page}&vote_count.gte=10";
    $res = curlGet($url);
    echo $res['body'];
    exit;
}

if ($endpoint === 'tmdb/popular' || $endpoint === 'popular') {
    $page = $_GET['page'] ?? 1;
    $url = TMDB_BASE_URL . "/movie/popular?api_key=" . TMDB_API_KEY . "&page={$page}";
    $res = curlGet($url);
    echo $res['body'];
    exit;
}

if ($endpoint === 'tmdb/search' || $endpoint === 'tmdb_search') {
    $q = $_GET['q'] ?? '';
    if (empty($q)) {
        echo json_encode(['results' => []]);
        exit;
    }
    $page = $_GET['page'] ?? 1;
    // Multi-search to find both Movies AND Web Series / TV Shows
    $url = TMDB_BASE_URL . "/search/multi?api_key=" . TMDB_API_KEY . "&query=" . urlencode($q) . "&page={$page}&include_adult=false";
    $res = curlGet($url);
    $data = json_decode($res['body'] ?? '{}', true);

    $results = [];
    if (isset($data['results']) && is_array($data['results'])) {
        foreach ($data['results'] as $item) {
            if (($item['media_type'] ?? '') === 'person') continue;
            $results[] = [
                'id' => $item['id'],
                'title' => $item['title'] ?? ($item['name'] ?? 'Untitled'),
                'original_title' => $item['original_title'] ?? ($item['original_name'] ?? ''),
                'media_type' => $item['media_type'] ?? 'movie',
                'poster_path' => $item['poster_path'] ?? null,
                'backdrop_path' => $item['backdrop_path'] ?? null,
                'overview' => $item['overview'] ?? '',
                'release_date' => $item['release_date'] ?? ($item['first_air_date'] ?? ''),
                'vote_average' => $item['vote_average'] ?? 0
            ];
        }
    }

    echo json_encode(['results' => $results]);
    exit;
}

if (preg_match('#^tmdb/(movie|tv)/(\d+)$#', $endpoint, $m) || $endpoint === 'movie' || $endpoint === 'tv') {
    $type = $m[1] ?? ($_GET['type'] ?? 'movie');
    $id = $m[2] ?? ($_GET['id'] ?? 0);
    $url = TMDB_BASE_URL . "/{$type}/{$id}?api_key=" . TMDB_API_KEY . "&append_to_response=external_ids,credits,videos,recommendations";
    $res = curlGet($url);
    if ($res['status'] === 200) {
        $body = json_decode($res['body'], true);
        if (is_array($body)) {
            if (!isset($body['title']) && isset($body['name'])) {
                $body['title'] = $body['name'];
            }
            if (!isset($body['original_title']) && isset($body['original_name'])) {
                $body['original_title'] = $body['original_name'];
            }
            if (!isset($body['release_date']) && isset($body['first_air_date'])) {
                $body['release_date'] = $body['first_air_date'];
            }
            $body['media_type'] = $type;
            echo json_encode($body);
            exit;
        }
    }
    echo $res['body'];
    exit;
}

// ----------------------------------------------------
// 2. VEGAMOVIES SEARCH
// ----------------------------------------------------

if ($endpoint === 'vegamovies/search' || $endpoint === 'vega_search') {
    $q = $_GET['q'] ?? '';
    $page = $_GET['page'] ?? 1;
    if (empty($q)) {
        echo json_encode(['found' => 0, 'hits' => []]);
        exit;
    }

    $searchUrl = VEGA_BASE . "/ts-search.php?q=" . urlencode($q) . "&page={$page}";
    $res = curlGet($searchUrl, VEGA_BASE . "/?s=" . urlencode($q), ['Accept: application/json, text/plain, */*']);
    
    if ($res['status'] !== 200) {
        http_response_code($res['status']);
        echo json_encode(['error' => "Vegamovies search returned status {$res['status']}"]);
        exit;
    }

    $data = json_decode($res['body'], true);
    $hits = [];
    if (isset($data['hits']) && is_array($data['hits'])) {
        foreach ($data['hits'] as $hit) {
            $doc = $hit['document'] ?? [];
            $postPath = $doc['permalink'] ?? '';
            if (strpos($postPath, 'http') === 0) {
                $parsed = parse_url($postPath);
                $postPath = $parsed['path'] ?? '';
            }
            $fullUrl = strpos($postPath, 'http') === 0 ? $postPath : VEGA_BASE . (strpos($postPath, '/') === 0 ? '' : '/') . $postPath;
            $hits[] = [
                'id' => $doc['id'] ?? '',
                'title' => $doc['post_title'] ?? '',
                'permalink' => $postPath,
                'fullUrl' => $fullUrl,
                'thumbnail' => $doc['post_thumbnail'] ?? '',
                'date' => $doc['post_date'] ?? ''
            ];
        }
    }

    echo json_encode(['found' => $data['found'] ?? count($hits), 'hits' => $hits]);
    exit;
}

// ----------------------------------------------------
// 3. VEGAMOVIES SCRAPER (Series/Seasons/Qualities/Drives)
// ----------------------------------------------------

if ($endpoint === 'vegamovies/scrape' || $endpoint === 'vega_scrape') {
    $targetUrl = $_GET['url'] ?? '';
    if (empty($targetUrl)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing url parameter']);
        exit;
    }

    if (strpos($targetUrl, 'http') !== 0) {
        $targetUrl = VEGA_BASE . '/' . ltrim($targetUrl, '/');
    }
    $targetUrl = preg_replace('#https?://[^/]+#', VEGA_BASE, $targetUrl);

    $res = curlGet($targetUrl);
    if ($res['status'] !== 200) {
        http_response_code($res['status']);
        echo json_encode(['error' => "Failed to fetch post: {$res['status']}"]);
        exit;
    }

    $html = $res['body'];

    // Title
    $pageTitle = '';
    if (preg_match('/<title>(.*?)<\/title>/i', $html, $tm)) {
        $pageTitle = trim(preg_replace('/\|\s*Vegamovies.*$/i', '', str_replace(['&#038;', '&#8211;'], ['&', '-'], $tm[1])));
    }

    // Audio Info
    $audioInfo = '';
    if (preg_match('/\{([^}]+)\}/i', $pageTitle, $lm) || 
        preg_match('/Dual Audio\s*\{?([^}\]|]+)\}?/i', $pageTitle, $lm) || 
        preg_match('/\[([^\]]*(?:Hindi|English|Tamil|Telugu|Dual|Multi)[^\]]*)\]/i', $pageTitle, $lm)) {
        $audioInfo = !empty($lm[1]) ? "Dual Audio {{$lm[1]}}" : trim($lm[0]);
    } elseif (preg_match('/Language:\s*([^<\n\r]+)/i', $html, $bm)) {
        $audioInfo = trim($bm[1]);
    }
    if (empty($audioInfo)) {
        if (stripos($pageTitle, 'dual audio') !== false) $audioInfo = 'Dual Audio [Hindi + English]';
        elseif (stripos($pageTitle, 'hindi dubbed') !== false || stripos($pageTitle, 'hindi org') !== false) $audioInfo = 'Hindi Dubbed / Multi Audio';
        else $audioInfo = 'Dual Audio {Hindi-English}';
    }

    // Detailed Audio Tracks Array
    $audioTracks = [];
    $searchScope = $pageTitle . ' ' . substr($html, 0, 15000);
    if (preg_match('/hindi/i', $searchScope)) $audioTracks[] = 'Hindi [ORG / Main]';
    if (preg_match('/english/i', $searchScope)) $audioTracks[] = 'English [Dual Audio]';
    if (preg_match('/tamil/i', $searchScope)) $audioTracks[] = 'Tamil';
    if (preg_match('/telugu/i', $searchScope)) $audioTracks[] = 'Telugu';
    if (preg_match('/malayalam/i', $searchScope)) $audioTracks[] = 'Malayalam';
    if (preg_match('/kannada/i', $searchScope)) $audioTracks[] = 'Kannada';
    if (preg_match('/bengali/i', $searchScope)) $audioTracks[] = 'Bengali';
    if (preg_match('/japanese/i', $searchScope)) $audioTracks[] = 'Japanese';
    if (preg_match('/korean/i', $searchScope)) $audioTracks[] = 'Korean';

    if (empty($audioTracks)) {
        $audioTracks = ['Hindi [ORG / Main]', 'English [Dual Audio]'];
    } elseif (count($audioTracks) === 1 && stripos($searchScope, 'dual audio') !== false) {
        if (!in_array('English [Dual Audio]', $audioTracks)) $audioTracks[] = 'English [Dual Audio]';
        elseif (!in_array('Hindi [ORG / Main]', $audioTracks)) $audioTracks[] = 'Hindi [ORG / Main]';
    }

    // Quality Info
    $qualityInfo = '';
    if (preg_match('/Quality:\s*([^<\n\r]+)/i', $html, $qm) || preg_match('/(480p\s*\|\|\s*720p\s*\|\|\s*1080p[^<\n\r]*)/i', $html, $qm)) {
        $qualityInfo = trim($qm[1]);
    }

    // Poster
    $poster = '';
    if (preg_match('/<img[^>]+src=["\']([^"\']+\.(?:jpg|jpeg|png|webp))["\'][^>]*class=["\'][^"\']*aligncenter/i', $html, $pm) ||
        preg_match('/<img[^>]+src=["\'](https:\/\/[^"\']+\/wp-content\/uploads\/[^"\']+\.(?:jpg|jpeg|png|webp))["\']/i', $html, $pm)) {
        $poster = $pm[1];
    }

    $isSeries = (bool)(preg_match('/season|episode|series/i', $targetUrl) || preg_match('/Season|Episode|Series/i', $pageTitle));

    // Parse elements
    preg_match_all('/<(h[1-6]|p|div)[^>]*>([\s\S]*?)<\/\1>/i', $html, $allMatches, PREG_SET_ORDER);
    $seasons = [];
    $movieQualities = [];
    $currentSeason = null;
    $lastHeading = '';

    foreach ($allMatches as $m) {
        $tag = strtolower($m[1]);
        $content = $m[2];
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($content)));

        if (strpos($tag, 'h') === 0 && strlen($text) > 4 && !preg_match('/screenshots|must see|official portal/i', $text)) {
            $lastHeading = $text;
        }

        if ($isSeries) {
            if (preg_match('/\b(?:Season|S)\s*(\d+)\b/i', $text, $sm) && (strpos($tag, 'h') === 0 || strpos($text, '**') !== false || stripos($text, 'season') !== false)) {
                $sNum = intval($sm[1]);
                $foundIdx = -1;
                foreach ($seasons as $k => $s) {
                    if ($s['seasonNumber'] === $sNum) { $foundIdx = $k; break; }
                }
                if ($foundIdx === -1) {
                    $newSeason = ['seasonNumber' => $sNum, 'title' => "Season {$sNum}", 'qualities' => []];
                    $seasons[] = $newSeason;
                    $currentSeason = &$seasons[count($seasons) - 1];
                } else {
                    $currentSeason = &$seasons[$foundIdx];
                }
            }
        }

        // Look for nexdrive links
        if (strpos($content, 'nexdrive.beer') !== false) {
            preg_match_all('/<a[^>]*href=["\'](https:\/\/nexdrive\.beer\/[^"\']+)["\'][^>]*>([\s\S]*?)<\/a>/i', $content, $lm, PREG_SET_ORDER);
            if (!empty($lm)) {
                $qual = '720p';
                $qRef = $lastHeading . ' ' . $text;
                if (preg_match('/2160p|4k/i', $qRef)) $qual = '4K 2160p';
                elseif (preg_match('/1080p/i', $qRef)) $qual = '1080p';
                elseif (preg_match('/720p/i', $qRef)) $qual = '720p';
                elseif (preg_match('/480p/i', $qRef)) $qual = '480p';

                $isHevc = (bool)preg_match('/hevc|x265|10bit/i', $qRef);
                $is60Fps = (bool)preg_match('/60fps/i', $qRef);
                $size = '';
                if (preg_match('/\[([0-9.]+\s*[MG]B[^\]]*)\]/i', $qRef, $szm)) {
                    $size = $szm[1];
                }

                $drives = [];
                foreach ($lm as $linkMatch) {
                    $btnText = trim(str_replace(['&#x26a1;', '&#8211;'], ['⚡', '-'], strip_tags($linkMatch[2])));
                    if (empty($btnText) || stripos($btnText, 'download') !== false) {
                        if (strpos($linkMatch[1], 'vcloud') !== false) $btnText = '⚡ V-Cloud';
                        elseif (strpos($linkMatch[1], 'fastdl') !== false) $btnText = '⚡ G-Direct';
                        else $btnText = '⚡ Fast Server';
                    }
                    $drives[] = [
                        'name' => $btnText,
                        'url' => $linkMatch[1]
                    ];
                }

                $qualityItem = [
                    'quality' => $qual,
                    'heading' => $lastHeading ?: substr($text, 0, 70),
                    'size' => $size,
                    'isHevc' => $isHevc,
                    'is60Fps' => $is60Fps,
                    'nexdriveUrl' => $drives[0]['url'] ?? '',
                    'drives' => $drives
                ];

                if ($isSeries && $currentSeason !== null) {
                    $currentSeason['qualities'][] = $qualityItem;
                } else {
                    $movieQualities[] = $qualityItem;
                }
            }
        }
    }

    if ($isSeries && empty($seasons) && !empty($movieQualities)) {
        $seasons[] = [
            'seasonNumber' => 1,
            'title' => 'Season 1',
            'qualities' => $movieQualities
        ];
    }

    echo json_encode([
        'title' => $pageTitle,
        'poster' => $poster,
        'audioInfo' => $audioInfo,
        'audioTracks' => $audioTracks,
        'qualityInfo' => $qualityInfo,
        'isSeries' => $isSeries,
        'seasons' => $isSeries ? $seasons : null,
        'qualities' => !$isSeries ? $movieQualities : ($seasons[0]['qualities'] ?? $movieQualities)
    ]);
    exit;
}

// ----------------------------------------------------
// 4. RESOLVE V-CLOUD & FASTDL HELPERS
// ----------------------------------------------------

function resolveVCloud($vcloudUrl) {
    if (!$vcloudUrl) return null;
    $res = curlGet($vcloudUrl, 'https://nexdrive.beer/');
    if ($res['status'] !== 200) return null;

    if (!preg_match('/var\s+url\s*=\s*atob\(atob\([\'"]([^\'"]+)[\'"]\)\)/i', $res['body'], $m)) {
        return null;
    }

    $tokenUrl = base64_decode(base64_decode($m[1]));
    $tRes = curlGet($tokenUrl, $vcloudUrl);
    if ($tRes['status'] !== 200) return null;

    $tHtml = $tRes['body'];
    $r2Url = preg_match('/href=[\'"](https:\/\/[^\'"]*r2\.cloudflarestorage\.com[^\'"]+)[\'"]/i', $tHtml, $rm) ? cleanUrl($rm[1]) : null;
    $pixeldrainUrl = preg_match('/href=[\'"](https:\/\/[^\'"]*pixeldrain[^\'"]+)[\'"]/i', $tHtml, $pm) ? cleanUrl($pm[1]) : null;
    $gofileUrl = preg_match('/href=[\'"](https:\/\/[^\'"]*gofile\.io[^\'"]+)[\'"]/i', $tHtml, $gm) ? cleanUrl($gm[1]) : null;
    $gpdlUrl = preg_match('/href=[\'"](https:\/\/[^\'"]*hubcloud\.ist[^\'"]+)[\'"]/i', $tHtml, $hm) ? cleanUrl($hm[1]) : null;

    return [
        'r2Url' => $r2Url,
        'pixeldrainUrl' => $pixeldrainUrl,
        'gofileUrl' => $gofileUrl,
        'gpdlUrl' => $gpdlUrl
    ];
}

function resolveFastDL($fastdlUrl, $referer = 'https://nexdrive.beer/') {
    if (!$fastdlUrl) return null;
    $res = curlGet($fastdlUrl, $referer);
    if ($res['status'] !== 200) return null;

    if (preg_match('/var\s+reurl\s*=\s*["\']([^"\']+)["\']/i', $res['body'], $m)) {
        $directFull = cleanUrl($m[1]);
        $parts = parse_url($directFull);
        parse_str($parts['query'] ?? '', $query);
        return $query['link'] ?? $directFull;
    }
    return null;
}

// ----------------------------------------------------
// 5. RESOLVE NEXDRIVE / DRIVE URL (Direct Playable Mirrors)
// ----------------------------------------------------

if ($endpoint === 'vegamovies/resolve-nexdrive' || $endpoint === 'resolve_nexdrive') {
    $targetUrl = $_GET['url'] ?? '';
    if (empty($targetUrl)) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing url parameter']);
        exit;
    }

    // 1. Direct V-Cloud URL
    if (strpos($targetUrl, 'vcloud.beer') !== false) {
        $vdata = resolveVCloud($targetUrl);
        $servers = [];
        if ($vdata) {
            if ($vdata['r2Url']) $servers[] = ['id' => 'r2', 'name' => 'Server 1: Cloudflare R2', 'badge' => 'Direct Stream (Seekable)', 'url' => $vdata['r2Url'], 'type' => 'stream'];
            if ($vdata['gpdlUrl']) $servers[] = ['id' => 'gpdl', 'name' => 'Server 2: HubCloud 10Gbps', 'badge' => '10Gbps Speed', 'url' => $vdata['gpdlUrl'], 'type' => 'download'];
            if ($vdata['pixeldrainUrl']) $servers[] = ['id' => 'pixeldrain', 'name' => 'Server 3: Pixeldrain', 'badge' => 'Direct Cloud', 'url' => $vdata['pixeldrainUrl'], 'type' => 'download'];
            if ($vdata['gofileUrl']) $servers[] = ['id' => 'gofile', 'name' => 'Server 4: Gofile', 'badge' => 'High Speed', 'url' => $vdata['gofileUrl'], 'type' => 'download'];
        }
        $primary = '';
        foreach ($servers as $s) { if ($s['type'] === 'stream') { $primary = $s['url']; break; } }
        if (empty($primary) && !empty($servers)) $primary = $servers[0]['url'];

        echo json_encode([
            'isEpisodeList' => false,
            'drives' => [['id' => 'vcloud', 'name' => '⚡ V-Cloud (Multi-Server)', 'servers' => $servers]],
            'servers' => $servers,
            'primaryStreamUrl' => $primary
        ]);
        exit;
    }

    // 2. Direct FastDL URL
    if (strpos($targetUrl, 'fastdl.beer') !== false) {
        $gUrl = resolveFastDL($targetUrl);
        $servers = [];
        if ($gUrl) {
            $servers[] = ['id' => 'google', 'name' => 'Server 1: Google UserContent', 'badge' => 'Instant Google Stream', 'url' => $gUrl, 'type' => 'stream'];
        }
        echo json_encode([
            'isEpisodeList' => false,
            'drives' => [['id' => 'gdirect', 'name' => '⚡ G-Direct (FastDL)', 'servers' => $servers]],
            'servers' => $servers,
            'primaryStreamUrl' => $servers[0]['url'] ?? ''
        ]);
        exit;
    }

    // 3. Nexdrive Page Resolution
    $res = curlGet($targetUrl);
    if ($res['status'] !== 200) {
        http_response_code($res['status']);
        echo json_encode(['error' => "Nexdrive returned status {$res['status']}"]);
        exit;
    }

    $html = $res['body'];

    // Check for Episode List
    $hasEpisodeHeadings = (bool)preg_match('/-:Episodes:|\bEpisode\s*\d+\b|\bEp\s*\d+\b/i', $html);
    if ($hasEpisodeHeadings) {
        preg_match_all('/<(?:h[1-6]|p|div)[^>]*>([\s\S]*?)<\/(?:h[1-6]|p|div)>/i', $html, $epMatches);
        $episodes = [];
        $currentEpLabel = '';

        foreach ($epMatches[1] as $content) {
            $text = trim(preg_replace('/\s+/', ' ', strip_tags($content)));
            if (preg_match('/episode|\bep\s*\d/i', $text) && strpos($content, 'href=') === false) {
                $currentEpLabel = trim(str_replace(['-', ':'], '', $text));
            } elseif (strpos($content, 'fastdl.beer') !== false || strpos($content, 'vcloud.beer') !== false || strpos($content, 'filebee.xyz') !== false) {
                $epFastDl = preg_match('/href=["\'](https:\/\/fastdl\.beer\/embed[^"\']+)["\']/i', $content, $fdm) ? $fdm[1] : null;
                $epVcloud = preg_match('/href=["\'](https:\/\/vcloud\.beer\/[^"\']+)["\']/i', $content, $vcm) ? $vcm[1] : null;
                $epFilepress = preg_match('/href=["\'](https:\/\/filebee\.xyz\/file\/[^"\']+)["\']/i', $content, $fpm) ? $fpm[1] : null;

                if ($epFastDl || $epVcloud || $epFilepress) {
                    $epIdx = count($episodes) + 1;
                    $mainUrl = $epFastDl ?: ($epVcloud ?: $epFilepress);
                    $episodes[] = [
                        'episodeNumber' => $epIdx,
                        'title' => $currentEpLabel ?: "Episode {$epIdx}",
                        'url' => $mainUrl,
                        'fastdlUrl' => $epFastDl,
                        'vcloudUrl' => $epVcloud,
                        'filepressUrl' => $epFilepress
                    ];
                    $currentEpLabel = '';
                }
            }
        }

        if (!empty($episodes)) {
            echo json_encode([
                'isEpisodeList' => true,
                'episodes' => $episodes
            ]);
            exit;
        }
    }

    // Movie Link: Extract all drives on page
    $fastdlUrl = preg_match('/href=["\'](https:\/\/fastdl\.beer\/embed[^"\']+)["\']/i', $html, $fm) ? $fm[1] : null;
    $vcloudUrl = preg_match('/href=["\'](https:\/\/vcloud\.beer\/[^"\']+)["\']/i', $html, $vm) ? $vm[1] : null;
    $filepressUrl = preg_match('/href=["\'](https:\/\/filebee\.xyz\/file\/[^"\']+)["\']/i', $html, $fpm) ? $fpm[1] : null;

    $drives = [];
    $allServers = [];

    // V-Cloud Drive
    if ($vcloudUrl) {
        $vdata = resolveVCloud($vcloudUrl);
        $vServers = [];
        if ($vdata) {
            if ($vdata['r2Url']) $vServers[] = ['id' => 'r2', 'name' => 'Server 1: Cloudflare R2', 'badge' => 'Direct Stream (Seekable)', 'url' => $vdata['r2Url'], 'type' => 'stream'];
            if ($vdata['gpdlUrl']) $vServers[] = ['id' => 'gpdl', 'name' => 'Server 2: HubCloud 10Gbps', 'badge' => '10Gbps Speed', 'url' => $vdata['gpdlUrl'], 'type' => 'download'];
            if ($vdata['pixeldrainUrl']) $vServers[] = ['id' => 'pixeldrain', 'name' => 'Server 3: Pixeldrain', 'badge' => 'Direct Cloud', 'url' => $vdata['pixeldrainUrl'], 'type' => 'download'];
            if ($vdata['gofileUrl']) $vServers[] = ['id' => 'gofile', 'name' => 'Server 4: Gofile', 'badge' => 'High Speed', 'url' => $vdata['gofileUrl'], 'type' => 'download'];
        }
        $drives[] = [
            'id' => 'vcloud',
            'name' => '⚡ V-Cloud (Multi-Server)',
            'servers' => $vServers
        ];
        $allServers = array_merge($allServers, $vServers);
    }

    // FastDL Drive
    if ($fastdlUrl) {
        $gUrl = resolveFastDL($fastdlUrl, $targetUrl);
        $gServers = [];
        if ($gUrl) {
            $gServers[] = ['id' => 'google', 'name' => 'Server: Google UserContent', 'badge' => 'Instant Google Stream', 'url' => $gUrl, 'type' => 'stream'];
        }
        $drives[] = [
            'id' => 'gdirect',
            'name' => '⚡ G-Direct (FastDL)',
            'servers' => $gServers
        ];
        $allServers = array_merge($allServers, $gServers);
    }

    // Filepress Drive
    if ($filepressUrl) {
        $fpServers = [['id' => 'filepress', 'name' => 'Server: Google Drive Mirror', 'badge' => 'G-Drive', 'url' => $filepressUrl, 'type' => 'download']];
        $drives[] = [
            'id' => 'filepress',
            'name' => '📁 Filepress (G-Drive)',
            'servers' => $fpServers
        ];
        $allServers = array_merge($allServers, $fpServers);
    }

    $primaryStreamUrl = '';
    foreach ($allServers as $s) {
        if ($s['type'] === 'stream') {
            $primaryStreamUrl = $s['url'];
            break;
        }
    }
    if (empty($primaryStreamUrl) && !empty($allServers)) {
        $primaryStreamUrl = $allServers[0]['url'];
    }

    echo json_encode([
        'isEpisodeList' => false,
        'drives' => $drives,
        'servers' => $allServers,
        'primaryStreamUrl' => $primaryStreamUrl
    ]);
    exit;
}

// Fallback error
http_response_code(404);
echo json_encode(['error' => "Endpoint '{$endpoint}' not found"]);
