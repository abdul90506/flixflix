<?php
// FLIXFLIX Dedicated Cinema Watch Page (PHP & Standalone Engine)
$initialUrl = isset($_GET['url']) ? trim($_GET['url']) : '';
$initialQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FlixFlix Cinema - Watch Movie & Series</title>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            brand: {
              red: '#e50914',
              redHover: '#b80710',
              dark: '#0a0b0e',
              card: '#12141a',
              border: '#1f242e',
              surface: '#171a23',
              emerald: '#10b981',
              cyan: '#06b6d4',
              amber: '#f59e0b'
            }
          }
        }
      }
    }
  </script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Dedicated Cinema Player Stylesheet -->
  <link rel="stylesheet" href="css/player.css">

  <style>
    body {
      background-color: #0a0b0e;
      color: #e2e8f0;
      font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .cinema-glow {
      box-shadow: 0 0 50px -10px rgba(229, 9, 20, 0.25);
    }
    .custom-scrollbar::-webkit-scrollbar {
      height: 6px;
      width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #12141a;
      border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #272d3b;
      border-radius: 9999px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
      background: #e50914;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col bg-brand-dark text-slate-100">

  <!-- ============================================ -->
  <!-- TOP NAVIGATION HEADER                        -->
  <!-- ============================================ -->
  <header class="sticky top-0 z-50 bg-[#0a0b0e]/95 backdrop-blur-md border-b border-brand-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
      
      <!-- Brand Logo & Back Button -->
      <div class="flex items-center gap-4">
        <a href="index.php" class="flex items-center gap-2 group">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-red to-orange-500 flex items-center justify-center shadow-lg shadow-brand-red/30 group-hover:scale-105 transition-transform">
            <i data-lucide="play" class="w-5 h-5 text-white fill-current ml-0.5"></i>
          </div>
          <span class="text-xl font-extrabold tracking-wider text-white">FLIX<span class="text-brand-red">FLIX</span></span>
        </a>

        <a href="index.php" class="hidden sm:flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-brand-card hover:bg-brand-surface text-gray-300 hover:text-white border border-brand-border transition-colors">
          <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
          <span>Home Catalog</span>
        </a>
      </div>

      <!-- Quick Search Bar -->
      <div class="flex-1 max-w-md relative">
        <div class="relative">
          <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input 
            type="text" 
            id="watchSearchInput" 
            placeholder="Search any movie or web series..." 
            class="w-full bg-brand-card/90 border border-brand-border text-xs rounded-xl pl-9 pr-8 py-2 text-white placeholder-gray-400 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red transition-all"
            autocomplete="off"
          >
          <button id="clearSearchBtn" onclick="clearWatchSearch()" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
          </button>
        </div>

        <!-- Search Suggestions Dropdown -->
        <div id="watchSearchDropdown" class="hidden absolute left-0 right-0 mt-2 bg-brand-card border border-brand-border rounded-xl shadow-2xl overflow-hidden z-50 max-h-80 overflow-y-auto custom-scrollbar"></div>
      </div>

      <!-- Direct Stream Status Badge -->
      <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-card border border-brand-border text-xs text-gray-300">
        <span class="w-2 h-2 rounded-full bg-brand-emerald animate-pulse"></span>
        <span class="font-medium text-[11px]">Direct Stream (No Proxy)</span>
      </div>

    </div>
  </header>

  <!-- ============================================ -->
  <!-- MAIN CINEMA THEATER CONTAINER                -->
  <!-- ============================================ -->
  <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Loading State Placeholder -->
    <div id="pageLoadingState" class="flex flex-col items-center justify-center py-20 space-y-4">
      <div class="w-14 h-14 border-4 border-brand-red border-t-transparent rounded-full animate-spin"></div>
      <p id="pageLoadingText" class="text-sm font-semibold text-gray-300">Connecting to VegaMovies & Resolving Streams...</p>
    </div>

    <!-- MAIN WATCH INTERFACE (Hidden while loading) -->
    <div id="watchInterface" class="hidden space-y-6">

      <!-- ============================================ -->
      <!-- 1. FULL WIDTH CINEMA VIDEO PLAYER            -->
      <!-- ============================================ -->
      <div class="relative w-full rounded-2xl overflow-hidden border border-brand-border cinema-glow bg-black">
        
        <div id="playerContainer" class="cinema-player-wrapper border-0">
          
          <!-- Native HTML5 Direct Stream Video Tag (No Proxy) -->
          <video 
            id="mainVideo" 
            class="cinema-video-el" 
            playsinline 
            preload="auto"
          ></video>

          <!-- Big Center Play / Pause Indicator -->
          <div id="centerPlayBtn" onclick="player.togglePlay()" class="cinema-center-play">
            <div class="cinema-center-play-circle cursor-pointer">
              <i id="centerPlayIcon" data-lucide="play" class="w-9 h-9 fill-current ml-1"></i>
            </div>
          </div>

          <!-- Player Loading Spinner Overlay -->
          <div id="playerLoading" class="hidden absolute inset-0 bg-black/90 flex flex-col items-center justify-center gap-3 z-30">
            <div class="w-12 h-12 border-4 border-brand-red border-t-transparent rounded-full animate-spin"></div>
            <p id="playerLoadingText" class="text-sm font-semibold text-gray-300">Connecting to direct source...</p>
          </div>

          <!-- Top Overlay Info Bar -->
          <div id="playerTopBar" class="absolute top-0 left-0 right-0 p-5 player-top-overlay flex items-center justify-between opacity-0 transition-opacity duration-300 z-20">
            <div class="flex items-center gap-3 flex-wrap">
              <h3 id="playerMovieTitle" class="text-sm font-bold text-white truncate max-w-md">Streaming</h3>
              <span id="playerActiveQualityBadge" class="text-[10px] font-bold px-2 py-0.5 rounded bg-brand-red text-white uppercase shadow-sm">HD</span>
              <span id="playerActiveServerBadge" class="text-[10px] font-semibold px-2 py-0.5 rounded bg-brand-surface border border-brand-border text-gray-300">Direct CDN</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-400 font-medium">FLIXFLIX PLAYER</span>
            </div>
          </div>

          <!-- Bottom Custom Controls Bar -->
          <div id="playerControlsBar" class="absolute bottom-0 left-0 right-0 p-4 player-bottom-overlay space-y-2 opacity-0 transition-opacity duration-300 z-20">
            
            <!-- Timeline Scrubber Container -->
            <div class="relative w-full flex items-center py-1">
              <div class="timeline-track-bg">
                <div id="playerBufferBar" class="timeline-buffer-fill"></div>
                <div id="playerProgressBar" class="timeline-progress-fill"></div>
              </div>
              <input 
                type="range" 
                id="timelineSlider" 
                class="custom-range-scrubber" 
                min="0" 
                max="100" 
                value="0" 
                step="0.1"
              >
            </div>

            <!-- Buttons & Actions Row -->
            <div class="flex items-center justify-between pt-1">
              
              <!-- Left: Play/Pause, Skip, Time, Volume -->
              <div class="flex items-center gap-3">
                <button onclick="player.togglePlay()" class="cinema-ctrl-btn hover:text-brand-red">
                  <i id="controlPlayIcon" data-lucide="play" class="w-5 h-5 fill-current"></i>
                </button>

                <button onclick="player.skip(-10)" class="cinema-ctrl-btn" title="Back 10s">
                  <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </button>

                <button onclick="player.skip(10)" class="cinema-ctrl-btn" title="Forward 10s">
                  <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                </button>

                <!-- Time Counters -->
                <div class="text-xs font-medium text-gray-300 flex items-center gap-1 select-none">
                  <span id="currentTimeDisplay">00:00:00</span>
                  <span class="text-gray-500">/</span>
                  <span id="durationDisplay">00:00:00</span>
                </div>

                <!-- Volume Slider -->
                <div class="flex items-center gap-2 ml-2">
                  <button onclick="player.toggleMute()" class="cinema-ctrl-btn">
                    <i id="volIcon" data-lucide="volume-2" class="w-4 h-4"></i>
                  </button>
                  <input 
                    type="range" 
                    id="volSlider" 
                    class="w-16 h-1 bg-gray-600 rounded-lg accent-brand-red cursor-pointer" 
                    min="0" 
                    max="1" 
                    step="0.05" 
                    value="1"
                  >
                </div>
              </div>

              <!-- Right: Audio Tracks, Speed, Theater, Fullscreen -->
              <div class="flex items-center gap-2">
                
                <!-- Multi-Audio Track Selector Button -->
                <div class="relative">
                  <button onclick="player.toggleAudioMenu()" class="cinema-ctrl-btn flex items-center gap-1.5 px-2 py-1 rounded bg-black/40 hover:bg-black/60 text-xs border border-white/10" title="Audio Tracks">
                    <i data-lucide="languages" class="w-3.5 h-3.5 text-brand-emerald"></i>
                    <span id="activeAudioLabel" class="text-[11px] font-semibold">Audio</span>
                  </button>
                  <div id="audioMenuDropdown" class="hidden absolute bottom-10 right-0 w-44 bg-brand-card border border-brand-border rounded-xl shadow-xl p-1 space-y-1 z-30">
                    <div class="text-[10px] uppercase font-bold text-gray-400 px-3 py-1 border-b border-brand-border">Audio Tracks</div>
                    <div id="audioMenuOptions" class="space-y-0.5"></div>
                  </div>
                </div>

                <!-- Playback Speed Selector Button -->
                <div class="relative">
                  <button onclick="player.toggleSpeedMenu()" class="cinema-ctrl-btn flex items-center gap-1 px-2 py-1 rounded bg-black/40 hover:bg-black/60 text-xs border border-white/10" title="Playback Speed">
                    <span id="activeSpeedLabel" class="text-[11px] font-semibold">1.0x</span>
                  </button>
                  <div id="speedMenuDropdown" class="hidden absolute bottom-10 right-0 w-32 bg-brand-card border border-brand-border rounded-xl shadow-xl p-1 space-y-1 z-30">
                    <div class="text-[10px] uppercase font-bold text-gray-400 px-3 py-1 border-b border-brand-border">Speed</div>
                    <div id="speedMenuOptions" class="space-y-0.5"></div>
                  </div>
                </div>

                <!-- PiP Button -->
                <button onclick="player.togglePiP()" class="cinema-ctrl-btn" title="Picture in Picture">
                  <i data-lucide="picture-in-picture-2" class="w-4 h-4"></i>
                </button>

                <!-- Fullscreen Button -->
                <button onclick="player.toggleFullscreen()" class="cinema-ctrl-btn hover:text-white" title="Fullscreen">
                  <i id="fsIcon" data-lucide="maximize" class="w-4 h-4"></i>
                </button>
              </div>

            </div>

          </div>

        </div>

      </div>

      <!-- ============================================ -->
      <!-- 2. HIERARCHICAL SELECTORS SUITE (RESPONSIVE) -->
      <!-- ============================================ -->
      <div class="space-y-4 bg-brand-card p-5 rounded-2xl border border-brand-border shadow-xl">
        
        <!-- Header Info -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-brand-border pb-4">
          <div>
            <h1 id="watchTitle" class="text-xl sm:text-2xl font-black text-white tracking-wide">Movie Title</h1>
            <div class="flex items-center gap-2 mt-1 flex-wrap">
              <span id="watchAudioBadge" class="text-xs px-2.5 py-0.5 rounded-full bg-brand-emerald/10 border border-brand-emerald/30 text-brand-emerald font-semibold">Multi-Audio</span>
              <span id="watchQualitySummary" class="text-xs text-gray-400">480p || 720p || 1080p || 4K</span>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-gray-400">Active Source:</span>
            <span id="watchActiveSourceLabel" class="text-xs font-bold text-white px-2.5 py-1 rounded-lg bg-brand-surface border border-brand-border">Cloudflare R2 Direct</span>
          </div>
        </div>

        <!-- SERIES: Season Selector (Shown only if isSeries) -->
        <div id="seasonSelectorSection" class="hidden space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
              <i data-lucide="layers" class="w-3.5 h-3.5 text-brand-red"></i>
              Select Season:
            </span>
            <span id="seasonCountLabel" class="text-xs text-gray-400">2 Seasons Available</span>
          </div>
          <div id="seasonsButtonsList" class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar"></div>
        </div>

        <!-- SERIES: Episode Selector (Shown only if isSeries) -->
        <div id="episodeSelectorSection" class="hidden space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
              <i data-lucide="film" class="w-3.5 h-3.5 text-brand-cyan"></i>
              Select Episode:
            </span>
            <span id="episodesCountLabel" class="text-xs text-gray-400">Loading episodes...</span>
          </div>
          <div id="episodesButtonsList" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2 max-h-48 overflow-y-auto p-1 custom-scrollbar"></div>
        </div>

        <!-- MOVIE & SERIES: Quality Selector -->
        <div class="space-y-2">
          <span class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
            <i data-lucide="sliders" class="w-3.5 h-3.5 text-brand-amber"></i>
            Select Quality / Resolution:
          </span>
          <div id="qualityButtonsList" class="flex items-center gap-2.5 overflow-x-auto pb-1 custom-scrollbar"></div>
        </div>

        <!-- MOVIE & SERIES: Audio Tracks Selector -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
              <i data-lucide="languages" class="w-3.5 h-3.5 text-brand-emerald"></i>
              Select Audio Track (Dual / Multi-Audio):
            </span>
            <span class="text-[11px] text-gray-400">Auto-detected tracks</span>
          </div>
          <div id="audioPillsContainer" class="flex items-center gap-2.5 overflow-x-auto pb-1 custom-scrollbar"></div>
        </div>

        <!-- MOVIE & SERIES: Drive Selector -->
        <div class="space-y-2">
          <span class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
            <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-brand-emerald"></i>
            Select Drive Provider:
          </span>
          <div id="driveButtonsList" class="flex items-center gap-2.5 overflow-x-auto pb-1 custom-scrollbar"></div>
        </div>

        <!-- DRIVE UNDERLYING SERVERS: Server / Mirror Selector -->
        <div class="space-y-2 pt-2 border-t border-brand-border">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-gray-300 uppercase tracking-wider flex items-center gap-1.5">
              <i data-lucide="server" class="w-3.5 h-3.5 text-brand-red"></i>
              Select Server / Driver Mirror:
            </span>
            <span class="text-[11px] text-gray-400">Click any server to stream or download</span>
          </div>
          <div id="serverSelectorList" class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar"></div>
        </div>

      </div>

      <!-- ============================================ -->
      <!-- 3. DIRECT DOWNLOAD & MIRRORS CENTER          -->
      <!-- ============================================ -->
      <div class="bg-brand-card p-5 rounded-2xl border border-brand-border shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-brand-border pb-3">
          <div class="flex items-center gap-2">
            <i data-lucide="download-cloud" class="w-5 h-5 text-brand-emerald"></i>
            <h3 class="text-base font-bold text-white">Direct Mirrors & Download Center</h3>
          </div>
          <span class="text-xs text-gray-400">All High-Speed Drivers</span>
        </div>

        <div id="downloadCardsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"></div>
      </div>

      <!-- ============================================ -->
      <!-- 4. MOVIE / SERIES SYNOPSIS & OVERVIEW        -->
      <!-- ============================================ -->
      <div class="bg-brand-card p-5 rounded-2xl border border-brand-border shadow-xl">
        <div class="flex flex-col md:flex-row gap-6">
          <img id="watchPoster" src="" alt="Poster" class="w-36 h-52 object-cover rounded-xl shadow-lg border border-brand-border flex-shrink-0 hidden">
          <div class="space-y-3 flex-1">
            <h2 id="synopsisTitle" class="text-lg font-bold text-white">Synopsis & Details</h2>
            <p id="watchOverview" class="text-sm text-gray-300 leading-relaxed">Loading details...</p>
            <div id="watchTags" class="flex items-center gap-2 flex-wrap pt-2"></div>
          </div>
        </div>
      </div>

    </div>

  </main>

  <!-- ============================================ -->
  <!-- FOOTER                                       -->
  <!-- ============================================ -->
  <footer class="bg-brand-card/60 border-t border-brand-border py-6 mt-12 text-center text-xs text-gray-500">
    <p>FlixFlix Cinema Streaming Engine &bull; Direct Cloud Playback &bull; Powered by VegaMovies</p>
  </footer>

  <!-- Dedicated Cinema Player Script -->
  <script src="js/player.js"></script>

  <!-- ============================================ -->
  <!-- WATCH PAGE CONTROLLER SCRIPT                 -->
  <!-- ============================================ -->
  <script>
    // Unified API Base URL (works seamlessly in any directory or server)
    const API_BASE = 'api.php?endpoint=';

    // Injected from PHP if available
    const INITIAL_URL = <?= json_encode($initialUrl) ?> || new URLSearchParams(window.location.search).get('url') || '';
    const INITIAL_QUERY = <?= json_encode($initialQuery) ?> || new URLSearchParams(window.location.search).get('q') || '';

    // Global State
    let scrapedData = null;
    let selectedSeasonIndex = 0;
    let selectedQualityIndex = 0;
    let selectedDriveIndex = 0;
    let selectedEpisodeIndex = 0;
    let currentEpisodesList = [];
    let currentDrivesList = [];
    let currentServersList = [];

    // Initialize Page
    document.addEventListener('DOMContentLoaded', async () => {
      // 1. Explicitly initialize player with DOM elements
      const videoEl = document.getElementById('mainVideo');
      const containerEl = document.getElementById('playerContainer');
      if (window.player) {
        window.player.init(videoEl, containerEl);
      }

      lucide.createIcons();

      // Bind search input on watch page
      setupWatchSearch();

      if (INITIAL_URL) {
        await loadPost(INITIAL_URL);
      } else if (INITIAL_QUERY) {
        searchAndLoadFirst(INITIAL_QUERY);
      } else {
        // Fallback default demo movie
        loadPost('https://new1.vegamovies.lease/download-avengers-infinity-war-2018-hindi-dubbed-480p-720p-1080p-2160p-4k/');
      }
    });

    // ----------------------------------------------------
    // LOAD & SCRAPE VEGAMOVIES POST
    // ----------------------------------------------------
    async function loadPost(postUrl) {
      showPageLoading('Scraping movie details, seasons, and download drives...');
      
      try {
        const res = await fetch(`${API_BASE}vegamovies/scrape&url=${encodeURIComponent(postUrl)}`);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        
        scrapedData = await res.json();
        console.log('[Watch] Scraped Data:', scrapedData);

        // Unhide interface so player element is live in the DOM
        hidePageLoading();

        renderPostDetails(scrapedData);

        // Determine if it's a series or movie
        if (scrapedData.isSeries && scrapedData.seasons && scrapedData.seasons.length > 0) {
          selectedSeasonIndex = 0;
          renderSeasonsSelector();
        } else {
          document.getElementById('seasonSelectorSection').classList.add('hidden');
          document.getElementById('episodeSelectorSection').classList.add('hidden');
          selectedQualityIndex = 0;
          renderQualitiesSelector(scrapedData.qualities || []);
        }

      } catch (err) {
        console.error('[Watch Error]:', err);
        showPageLoading(`Error: ${err.message}. Please try searching another title.`);
      }
    }

    // ----------------------------------------------------
    // RENDER DETAILS & POSTER
    // ----------------------------------------------------
    function renderPostDetails(data) {
      document.getElementById('watchTitle').innerText = data.title;
      document.getElementById('playerMovieTitle').innerText = data.title;
      document.getElementById('synopsisTitle').innerText = data.title;
      document.getElementById('watchOverview').innerText = `${data.title}. Direct stream and mirror downloads extracted from VegaMovies.`;

      const audioBadge = document.getElementById('watchAudioBadge');
      if (audioBadge) {
        audioBadge.innerText = data.audioInfo || 'Dual Audio';
      }

      const qualSummary = document.getElementById('watchQualitySummary');
      if (qualSummary) {
        qualSummary.innerText = data.qualityInfo || 'Multiple Qualities';
      }

      const posterImg = document.getElementById('watchPoster');
      if (data.poster) {
        posterImg.src = data.poster;
        posterImg.classList.remove('hidden');
      } else {
        posterImg.classList.add('hidden');
      }

      // Configure player audio tracks
      if (player) {
        const tracks = (data.audioTracks && data.audioTracks.length > 0) 
          ? data.audioTracks 
          : ['Hindi [ORG / Main]', 'English [Dual Audio]'];
        player.setAudioTracks(tracks);
      }
    }

    // ----------------------------------------------------
    // SERIES: SEASONS SELECTOR
    // ----------------------------------------------------
    function renderSeasonsSelector() {
      const container = document.getElementById('seasonsButtonsList');
      const section = document.getElementById('seasonSelectorSection');
      section.classList.remove('hidden');

      const seasons = scrapedData.seasons;
      document.getElementById('seasonCountLabel').innerText = `${seasons.length} Season(s) Available`;

      container.innerHTML = seasons.map((s, idx) => {
        const isActive = idx === selectedSeasonIndex;
        return `
          <button 
            onclick="selectSeason(${idx})" 
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 flex-shrink-0 ${
              isActive 
                ? 'bg-brand-red text-white shadow-lg shadow-brand-red/30 border border-brand-red' 
                : 'bg-brand-surface text-gray-300 hover:text-white border border-brand-border hover:border-gray-500'
            }"
          >
            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
            <span>${s.title}</span>
            <span class="text-[10px] px-1.5 py-0.5 rounded ${isActive ? 'bg-black/30' : 'bg-brand-card'} text-gray-200">
              ${s.qualities ? s.qualities.length : 0} Qualities
            </span>
          </button>
        `;
      }).join('');

      lucide.createIcons();

      // Load qualities for selected season
      const activeSeason = seasons[selectedSeasonIndex];
      selectedQualityIndex = 0;
      renderQualitiesSelector(activeSeason.qualities || []);
    }

    function selectSeason(idx) {
      selectedSeasonIndex = idx;
      renderSeasonsSelector();
    }

    // ----------------------------------------------------
    // QUALITIES SELECTOR
    // ----------------------------------------------------
    function renderQualitiesSelector(qualities) {
      const container = document.getElementById('qualityButtonsList');
      if (!qualities || qualities.length === 0) {
        container.innerHTML = `<span class="text-xs text-gray-400">No qualities available for this selection.</span>`;
        return;
      }

      container.innerHTML = qualities.map((q, idx) => {
        const isActive = idx === selectedQualityIndex;
        return `
          <button 
            onclick="selectQuality(${idx})" 
            class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 flex-shrink-0 ${
              isActive 
                ? 'bg-brand-amber text-black shadow-lg shadow-brand-amber/20 font-black border border-brand-amber' 
                : 'bg-brand-surface text-gray-300 hover:text-white border border-brand-border hover:border-gray-500'
            }"
          >
            <span>${q.quality}</span>
            ${q.size ? `<span class="text-[10px] px-1.5 py-0.5 rounded ${isActive ? 'bg-black/20 text-black font-semibold' : 'bg-brand-card text-gray-400'}">${q.size}</span>` : ''}
            ${q.isHevc ? `<span class="text-[9px] px-1 py-0.2 rounded bg-black/30 uppercase">HEVC</span>` : ''}
          </button>
        `;
      }).join('');

      lucide.createIcons();

      // Update badge in player
      const activeQ = qualities[selectedQualityIndex];
      const qBadge = document.getElementById('playerActiveQualityBadge');
      if (qBadge && activeQ) qBadge.innerText = activeQ.quality;

      // Render Drives for this quality
      renderDrivesSelector(activeQ ? (activeQ.drives || [{ name: '⚡ Direct Link', url: activeQ.nexdriveUrl }]) : []);
    }

    function selectQuality(idx) {
      selectedQualityIndex = idx;
      const qualities = scrapedData.isSeries 
        ? scrapedData.seasons[selectedSeasonIndex].qualities 
        : scrapedData.qualities;
      renderQualitiesSelector(qualities);
    }

    // ----------------------------------------------------
    // DRIVES SELECTOR (V-Cloud, G-Direct, Filepress, Batch)
    // ----------------------------------------------------
    function renderDrivesSelector(drives) {
      currentDrivesList = drives;
      const container = document.getElementById('driveButtonsList');

      if (!drives || drives.length === 0) {
        container.innerHTML = `<span class="text-xs text-gray-400">No download drives found.</span>`;
        return;
      }

      if (selectedDriveIndex >= drives.length) {
        selectedDriveIndex = 0;
      }

      container.innerHTML = drives.map((d, idx) => {
        const isActive = idx === selectedDriveIndex;
        return `
          <button 
            onclick="selectDrive(${idx})" 
            class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 flex-shrink-0 ${
              isActive 
                ? 'bg-brand-emerald text-black font-black shadow-lg shadow-brand-emerald/20 border border-brand-emerald' 
                : 'bg-brand-surface text-gray-300 hover:text-white border border-brand-border hover:border-gray-500'
            }"
          >
            <span>${d.name}</span>
          </button>
        `;
      }).join('');

      lucide.createIcons();

      // Resolve the selected drive
      resolveCurrentDrive(drives[selectedDriveIndex].url);
    }

    function selectDrive(idx) {
      selectedDriveIndex = idx;
      renderDrivesSelector(currentDrivesList);
    }

    // ----------------------------------------------------
    // RESOLVE ACTIVE DRIVE (Episodes or Movie Servers)
    // ----------------------------------------------------
    async function resolveCurrentDrive(driveUrl) {
      player.showSpinner('Resolving drive mirrors & servers...');

      try {
        const res = await fetch(`${API_BASE}vegamovies/resolve-nexdrive&url=${encodeURIComponent(driveUrl)}`);
        if (!res.ok) throw new Error(`Resolver returned ${res.status}`);

        const resolved = await res.json();
        console.log('[Watch] Resolved Drive:', resolved);

        if (resolved.isEpisodeList && resolved.episodes && resolved.episodes.length > 0) {
          // This is a series episode list!
          currentEpisodesList = resolved.episodes;
          renderEpisodesSelector(currentEpisodesList);
        } else {
          // This is a direct video / movie link with servers
          document.getElementById('episodeSelectorSection').classList.add('hidden');
          currentServersList = resolved.servers || [];
          renderServersSelector(currentServersList);
          renderDownloadCards(currentServersList);

          if (resolved.primaryStreamUrl) {
            player.loadSource(resolved.primaryStreamUrl);
          } else if (currentServersList.length > 0) {
            player.loadSource(currentServersList[0].url);
          }
        }

        player.hideSpinner();
      } catch (err) {
        player.hideSpinner();
        alert('Could not resolve drive: ' + err.message);
      }
    }

    // ----------------------------------------------------
    // SERIES: EPISODES SELECTOR
    // ----------------------------------------------------
    function renderEpisodesSelector(episodes) {
      const section = document.getElementById('episodeSelectorSection');
      section.classList.remove('hidden');

      const container = document.getElementById('episodesButtonsList');
      document.getElementById('episodesCountLabel').innerText = `${episodes.length} Episodes in this season`;

      if (selectedEpisodeIndex >= episodes.length) selectedEpisodeIndex = 0;

      container.innerHTML = episodes.map((ep, idx) => {
        const isActive = idx === selectedEpisodeIndex;
        return `
          <button 
            onclick="selectEpisode(${idx})" 
            class="p-2.5 rounded-xl text-xs font-bold text-center border transition-all flex flex-col items-center justify-center gap-1 ${
              isActive 
                ? 'bg-brand-cyan text-black border-brand-cyan font-black shadow-lg shadow-brand-cyan/20 ring-2 ring-brand-cyan/50' 
                : 'bg-brand-surface text-gray-300 hover:text-white border-brand-border hover:border-gray-500'
            }"
          >
            <div class="flex items-center gap-1">
              <i data-lucide="play-circle" class="w-3.5 h-3.5"></i>
              <span>EP ${String(ep.episodeNumber).padStart(2, '0')}</span>
            </div>
            <span class="text-[10px] text-gray-400 font-normal truncate max-w-[90px]">${ep.title}</span>
          </button>
        `;
      }).join('');

      lucide.createIcons();

      // Resolve the selected episode
      resolveCurrentEpisode(episodes[selectedEpisodeIndex]);
    }

    async function selectEpisode(idx) {
      selectedEpisodeIndex = idx;
      renderEpisodesSelector(currentEpisodesList);
    }

    async function resolveCurrentEpisode(episode) {
      player.showSpinner(`Resolving ${episode.title}...`);
      
      try {
        const epUrl = episode.url || episode.fastdlUrl || episode.vcloudUrl;
        const res = await fetch(`${API_BASE}vegamovies/resolve-nexdrive&url=${encodeURIComponent(epUrl)}`);
        const data = await res.json();

        currentServersList = data.servers || [];
        renderServersSelector(currentServersList);
        renderDownloadCards(currentServersList);

        if (data.primaryStreamUrl) {
          player.loadSource(data.primaryStreamUrl);
        } else if (currentServersList.length > 0) {
          player.loadSource(currentServersList[0].url);
        }
        player.hideSpinner();
      } catch (err) {
        player.hideSpinner();
        alert('Episode resolve error: ' + err.message);
      }
    }

    // ----------------------------------------------------
    // SERVERS SELECTOR (Underlying Drive Mirrors)
    // ----------------------------------------------------
    function renderServersSelector(servers) {
      const container = document.getElementById('serverSelectorList');
      if (!servers || servers.length === 0) {
        container.innerHTML = `<span class="text-xs text-gray-400">No streamable servers found.</span>`;
        return;
      }

      if (player) {
        player.servers = servers;
        player.currentServerIndex = 0;
      }

      container.innerHTML = servers.map((s, idx) => {
        const isDefault = idx === 0;
        return `
          <button 
            onclick="switchWatchServer(${idx})" 
            id="watchServerBtn_${idx}"
            class="px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all flex items-center gap-2 flex-shrink-0 ${
              isDefault 
                ? 'bg-brand-red text-white border-brand-red font-bold shadow-md' 
                : 'bg-brand-surface text-gray-300 hover:text-white border-brand-border'
            }"
          >
            <span class="w-2 h-2 rounded-full ${s.type === 'stream' ? 'bg-brand-emerald animate-pulse' : 'bg-gray-400'}"></span>
            <span>${s.name}</span>
            ${s.badge ? `<span class="text-[9px] uppercase px-1.5 py-0.5 rounded bg-black/40 text-gray-300">${s.badge}</span>` : ''}
          </button>
        `;
      }).join('');

      updateActiveSourceLabel(servers[0]?.name || 'Direct Stream');
      lucide.createIcons();
    }

    function switchWatchServer(idx) {
      const selected = currentServersList[idx];
      if (!selected) return;

      currentServersList.forEach((_, i) => {
        const btn = document.getElementById(`watchServerBtn_${i}`);
        if (btn) {
          if (i === idx) {
            btn.className = 'px-3.5 py-2 rounded-xl text-xs font-bold border bg-brand-red text-white border-brand-red shadow-md transition-all flex items-center gap-2 flex-shrink-0';
          } else {
            btn.className = 'px-3.5 py-2 rounded-xl text-xs font-semibold border bg-brand-surface text-gray-300 hover:text-white border-brand-border transition-all flex items-center gap-2 flex-shrink-0';
          }
        }
      });

      updateActiveSourceLabel(selected.name);

      if (selected.type === 'stream' || selected.id === 'r2' || selected.id === 'google') {
        player.loadSource(selected.url);
      } else {
        // Open mirror download
        window.open(selected.url, '_blank');
      }
    }

    function updateActiveSourceLabel(name) {
      const el = document.getElementById('watchActiveSourceLabel');
      if (el) el.innerText = name;
      const pBadge = document.getElementById('playerActiveServerBadge');
      if (pBadge) pBadge.innerText = name.split(':')[0] || 'CDN';
    }

    // ----------------------------------------------------
    // DOWNLOAD CARDS
    // ----------------------------------------------------
    function renderDownloadCards(servers) {
      const container = document.getElementById('downloadCardsContainer');
      if (!servers || servers.length === 0) {
        container.innerHTML = `<span class="text-xs text-gray-400">No download mirrors available.</span>`;
        return;
      }

      container.innerHTML = servers.map(s => {
        return `
          <div class="p-3.5 rounded-xl bg-brand-surface border border-brand-border hover:border-gray-500 transition-all flex items-center justify-between gap-3">
            <div class="space-y-0.5 min-w-0">
              <p class="text-xs font-bold text-white truncate">${s.name}</p>
              <p class="text-[10px] text-gray-400 font-medium">${s.badge || 'Direct Mirror'}</p>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <a 
                href="${s.url}" 
                target="_blank" 
                download 
                class="px-2.5 py-1.5 rounded-lg bg-brand-red hover:bg-brand-redHover text-white text-xs font-bold flex items-center gap-1 shadow-sm transition-colors"
              >
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                <span>Download</span>
              </a>
              <button 
                onclick="navigator.clipboard.writeText('${s.url}'); alert('Link copied to clipboard!');" 
                class="p-1.5 rounded-lg bg-brand-card hover:bg-brand-border text-gray-300 hover:text-white border border-brand-border transition-colors"
                title="Copy Direct Link"
              >
                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
              </button>
            </div>
          </div>
        `;
      }).join('');

      lucide.createIcons();
    }

    // ----------------------------------------------------
    // WATCH SEARCH UTILITIES
    // ----------------------------------------------------
    let searchDebounce = null;
    function setupWatchSearch() {
      const input = document.getElementById('watchSearchInput');
      const dropdown = document.getElementById('watchSearchDropdown');
      const clearBtn = document.getElementById('clearSearchBtn');

      input.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        clearBtn.classList.toggle('hidden', query.length === 0);

        clearTimeout(searchDebounce);
        if (query.length < 2) {
          dropdown.classList.add('hidden');
          return;
        }

        searchDebounce = setTimeout(async () => {
          try {
            const res = await fetch(`${API_BASE}vegamovies/search&q=${encodeURIComponent(query)}`);
            const data = await res.json();
            if (data.hits && data.hits.length > 0) {
              dropdown.innerHTML = data.hits.map(h => `
                <div 
                  onclick="loadNewMovie('${h.fullUrl}')" 
                  class="p-3 hover:bg-brand-surface cursor-pointer flex items-center gap-3 border-b border-brand-border/40 last:border-0 transition-colors"
                >
                  <img src="${h.thumbnail || '/placeholder.png'}" class="w-10 h-14 object-cover rounded bg-brand-dark flex-shrink-0">
                  <div class="min-w-0">
                    <p class="text-xs font-bold text-white truncate">${h.title}</p>
                    <p class="text-[10px] text-gray-400">${h.date || 'VegaMovies'}</p>
                  </div>
                </div>
              `).join('');
              dropdown.classList.remove('hidden');
            } else {
              dropdown.innerHTML = `<div class="p-4 text-center text-xs text-gray-400">No results found on VegaMovies</div>`;
              dropdown.classList.remove('hidden');
            }
          } catch (e) {
            console.error('Search error:', e);
          }
        }, 300);
      });

      document.addEventListener('click', (e) => {
        if (!e.target.closest('#watchSearchInput') && !e.target.closest('#watchSearchDropdown')) {
          dropdown.classList.add('hidden');
        }
      });
    }

    function clearWatchSearch() {
      document.getElementById('watchSearchInput').value = '';
      document.getElementById('clearSearchBtn').classList.add('hidden');
      document.getElementById('watchSearchDropdown').classList.add('hidden');
    }

    function loadNewMovie(url) {
      clearWatchSearch();
      window.history.pushState({}, '', `watch.php?url=${encodeURIComponent(url)}`);
      loadPost(url);
    }

    async function searchAndLoadFirst(query) {
      try {
        const res = await fetch(`${API_BASE}vegamovies/search&q=${encodeURIComponent(query)}`);
        const data = await res.json();
        if (data.hits && data.hits.length > 0) {
          loadPost(data.hits[0].fullUrl);
        } else {
          showPageLoading(`No movies found for "${query}".`);
        }
      } catch (e) {
        showPageLoading(`Search error: ${e.message}`);
      }
    }

    // ----------------------------------------------------
    // UI HELPERS
    // ----------------------------------------------------
    function showPageLoading(text) {
      document.getElementById('pageLoadingState').classList.remove('hidden');
      document.getElementById('pageLoadingText').innerText = text;
      document.getElementById('watchInterface').classList.add('hidden');
    }

    function hidePageLoading() {
      document.getElementById('pageLoadingState').classList.add('hidden');
      document.getElementById('watchInterface').classList.remove('hidden');
      lucide.createIcons();
    }
  </script>

</body>
</html>
