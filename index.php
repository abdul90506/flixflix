<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FLIXFLIX - Unlimited Movies & Web Series</title>
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            sans: ['Outfit', 'Inter', 'sans-serif'],
          },
          colors: {
            brand: {
              red: '#e50914',
              hoverRed: '#f40612',
              cyan: '#00e5ff',
              emerald: '#10b981',
              dark: '#0a0b0e',
              card: '#12141a',
              cardHover: '#1a1d26',
              border: '#222634'
            }
          }
        }
      }
    }
  </script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Cinema Player Dedicated Stylesheet -->
  <link rel="stylesheet" href="css/player.css">

  <style>
    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #0a0b0e; }
    ::-webkit-scrollbar-thumb { background: #222634; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #e50914; }

    .poster-hover:hover img {
      transform: scale(1.05);
    }
    .glass-nav {
      background: rgba(10, 11, 14, 0.88);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }
  </style>
</head>

<body class="bg-[#0a0b0e] text-gray-100 min-h-screen font-sans antialiased overflow-x-hidden">

  <!-- ============================================== -->
  <!-- TOP NAVIGATION BAR -->
  <!-- ============================================== -->
  <nav class="fixed top-0 left-0 right-0 z-50 glass-nav border-b border-brand-border/60 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
      
      <!-- Brand Logo -->
      <a href="#" onclick="goHome(event)" class="flex items-center gap-2.5 group flex-shrink-0">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-red to-orange-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-brand-red/30 group-hover:scale-105 transition-transform">
          <i data-lucide="play" class="w-5 h-5 fill-current ml-0.5"></i>
        </div>
        <div>
          <span class="text-2xl font-black tracking-wider bg-gradient-to-r from-white via-gray-200 to-brand-red bg-clip-text text-transparent">FLIX<span class="text-brand-red">FLIX</span></span>
          <span class="hidden sm:inline-block text-[10px] uppercase font-bold tracking-widest text-brand-emerald ml-2 px-1.5 py-0.5 rounded bg-brand-emerald/10 border border-brand-emerald/30">VegaMovies Engine</span>
        </div>
      </a>

      <!-- Real-Time Search Bar -->
      <div class="flex-1 max-w-2xl relative">
        <div class="relative flex items-center">
          <i data-lucide="search" class="w-5 h-5 absolute left-3.5 text-gray-400 pointer-events-none"></i>
          <input 
            type="text" 
            id="searchInput" 
            placeholder="Search Movies & Web Series (e.g. Avengers, VisionQuest) or paste VegaMovies link..." 
            class="w-full bg-[#12141a] border border-brand-border rounded-full py-2.5 pl-11 pr-10 text-sm placeholder-gray-400 focus:outline-none focus:border-brand-red focus:ring-1 focus:ring-brand-red transition-all shadow-inner"
            autocomplete="off"
          />
          <button id="clearSearchBtn" onclick="clearSearch()" class="hidden absolute right-3 text-gray-400 hover:text-white">
            <i data-lucide="x" class="w-4 h-4"></i>
          </button>
        </div>

        <!-- Live Instant Suggestions Dropdown -->
        <div id="searchDropdown" class="hidden absolute top-full left-0 right-0 mt-2 bg-[#12141a] border border-brand-border rounded-2xl shadow-2xl overflow-hidden z-50 max-h-96 overflow-y-auto">
          <div id="searchDropdownContent" class="p-2 space-y-1">
            <!-- Populated via JavaScript -->
          </div>
        </div>
      </div>

      <!-- Quick Action: Direct Link Modal -->
      <div class="flex items-center gap-3">
        <button onclick="openDirectUrlModal()" class="flex items-center gap-1.5 text-xs font-semibold px-3.5 py-2 rounded-full bg-brand-border hover:bg-brand-red hover:text-white text-gray-300 transition-all border border-gray-700/50">
          <i data-lucide="link" class="w-3.5 h-3.5 text-brand-cyan"></i>
          <span class="hidden md:inline">Paste VegaMovies URL</span>
        </button>
      </div>

    </div>
  </nav>

  <!-- ============================================== -->
  <!-- DIRECT URL MODAL -->
  <!-- ============================================== -->
  <div id="urlModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-brand-card border border-brand-border rounded-3xl max-w-lg w-full p-6 shadow-2xl relative">
      <button onclick="closeDirectUrlModal()" class="absolute top-5 right-5 text-gray-400 hover:text-white">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-brand-emerald/20 text-brand-emerald flex items-center justify-center">
          <i data-lucide="link-2" class="w-5 h-5"></i>
        </div>
        <div>
          <h3 class="text-lg font-bold text-white">Direct VegaMovies Link</h3>
          <p class="text-xs text-gray-400">Paste any VegaMovies movie or web series URL</p>
        </div>
      </div>
      <div class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-300 mb-1">VegaMovies URL</label>
          <input 
            type="text" 
            id="modalDirectUrlInput" 
            placeholder="https://new1.vegamovies.lease/download-avengers-infinity-war-2018-hindi-dubbed-480p-720p-1080p-2160p-4k/" 
            class="w-full bg-[#0a0b0e] border border-brand-border rounded-xl px-4 py-3 text-sm focus:border-brand-red focus:outline-none"
            value="https://new1.vegamovies.lease/download-avengers-infinity-war-2018-hindi-dubbed-480p-720p-1080p-2160p-4k/"
          />
        </div>
        <div class="flex justify-end gap-2">
          <button onclick="closeDirectUrlModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-400 hover:text-white">Cancel</button>
          <button onclick="submitDirectUrl()" class="px-5 py-2.5 rounded-xl text-xs font-bold bg-brand-red hover:bg-brand-hoverRed text-white shadow-lg shadow-brand-red/30 transition-all flex items-center gap-2">
            <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i>
            Parse & Play
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- VEGAMOVIES VERSION / SEASON SELECTION MODAL -->
  <!-- ============================================== -->
  <div id="vegaVersionsModal" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-md flex items-center justify-center p-4">
    <div class="bg-brand-card border border-brand-border rounded-3xl max-w-2xl w-full p-6 shadow-2xl relative max-h-[85vh] flex flex-col">
      <button onclick="closeVegaVersionsModal()" class="absolute top-5 right-5 text-gray-400 hover:text-white p-1 rounded-lg hover:bg-brand-border transition-colors">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
      <div class="flex items-center gap-3 mb-4 pb-3 border-b border-brand-border/60">
        <div class="w-10 h-10 rounded-xl bg-brand-emerald/20 text-brand-emerald flex items-center justify-center flex-shrink-0">
          <i data-lucide="layers" class="w-5 h-5"></i>
        </div>
        <div>
          <h3 id="vegaModalTitle" class="text-lg font-bold text-white">Select VegaMovies Release</h3>
          <p id="vegaModalSubtitle" class="text-xs text-gray-400">Multiple versions/seasons found on VegaMovies. Choose one to stream:</p>
        </div>
      </div>
      <div id="vegaVersionsList" class="space-y-3 overflow-y-auto pr-1 flex-1 scrollbar-none">
        <!-- populated dynamically -->
      </div>
    </div>
  </div>

  <!-- Toast Notification -->
  <div id="searchToast" class="hidden fixed bottom-6 right-6 z-50 bg-brand-card border border-brand-emerald/40 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3">
    <div class="w-4 h-4 border-2 border-brand-emerald border-t-transparent rounded-full animate-spin"></div>
    <span id="searchToastText" class="text-xs font-semibold">Searching VegaMovies...</span>
  </div>

  <!-- ============================================== -->
  <!-- MAIN CONTAINER -->
  <!-- ============================================== -->
  <main class="pt-24 pb-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

    <!-- ============================================ -->
    <!-- CINEMA PLAYER SUITE (Custom Dedicated Engine) -->
    <!-- ============================================ -->
    <section id="playerSection" class="hidden transition-all duration-500">
      <div class="mb-4 flex items-center justify-between">
        <button onclick="closePlayer()" class="flex items-center gap-2 text-sm font-semibold text-gray-400 hover:text-white transition-colors py-1.5 px-3 rounded-lg hover:bg-brand-card">
          <i data-lucide="arrow-left" class="w-4 h-4"></i>
          Back to Catalog
        </button>
        <div id="playerStatusBadge" class="flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-brand-card border border-brand-border text-gray-300">
          <span class="w-2 h-2 rounded-full bg-brand-emerald animate-pulse"></span>
          <span>VegaMovies Stream Active</span>
        </div>
      </div>

      <!-- Player & Info Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Player Column (2 Cols) -->
        <div class="lg:col-span-2 space-y-4">
          
          <!-- DRIVER MIRRORS SELECTOR BAR (Select which mirror to stream from) -->
          <div class="bg-brand-card border border-brand-border rounded-2xl p-3 space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="font-bold text-gray-300 flex items-center gap-1.5">
                <i data-lucide="server" class="w-3.5 h-3.5 text-brand-red"></i> Select Driver Server / Mirror:
              </span>
              <span class="text-[11px] text-gray-400">Click any server to switch stream</span>
            </div>
            <div id="serverPillsContainer" class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
              <!-- Dynamically populated server pills -->
            </div>
          </div>

          <!-- DEDICATED CINEMA VIDEO PLAYER WRAPPER -->
          <div id="playerContainer" class="cinema-player-wrapper border border-brand-border">
            
            <!-- Native HTML5 Video Element -->
            <video 
              id="mainVideo" 
              class="cinema-video-el" 
              playsinline
              preload="auto"
            ></video>

            <!-- Big Center Play / Pause Indicator -->
            <div id="centerPlayBtn" onclick="player.togglePlay()" class="cinema-center-play">
              <div class="cinema-center-play-circle">
                <i id="centerPlayIcon" data-lucide="play" class="w-9 h-9 fill-current ml-1"></i>
              </div>
            </div>

            <!-- Player Loading Spinner Overlay -->
            <div id="playerLoading" class="hidden absolute inset-0 bg-black/90 flex flex-col items-center justify-center gap-3 z-30">
              <div class="w-12 h-12 border-4 border-brand-red border-t-transparent rounded-full animate-spin"></div>
              <p id="playerLoadingText" class="text-sm font-semibold text-gray-300">Connecting to stream...</p>
            </div>

            <!-- Top Overlay Info Bar -->
            <div id="playerTopBar" class="absolute top-0 left-0 right-0 p-5 player-top-overlay flex items-center justify-between opacity-0 transition-opacity duration-300 z-20">
              <div class="flex items-center gap-3">
                <h3 id="playerMovieTitle" class="text-sm font-bold text-white truncate max-w-md">Avengers: Infinity War (2018)</h3>
                <span id="playerActiveQualityBadge" class="text-[10px] font-bold px-2 py-0.5 rounded bg-brand-red text-white uppercase shadow-sm">480p SD</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs text-brand-emerald font-semibold flex items-center gap-1">
                  <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Stream Connected
                </span>
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
                  min="0" 
                  max="100" 
                  value="0" 
                  step="0.05" 
                  class="custom-range-scrubber" 
                />
              </div>

              <!-- Controls Buttons Row -->
              <div class="flex items-center justify-between gap-3 pt-1">
                
                <!-- Left Buttons -->
                <div class="flex items-center gap-3">
                  <!-- Play/Pause -->
                  <button onclick="player.togglePlay()" class="cinema-btn" title="Play/Pause (Space)">
                    <i id="controlPlayIcon" data-lucide="play" class="w-5 h-5 fill-current"></i>
                  </button>

                  <!-- Rewind 10s -->
                  <button onclick="player.skip(-10)" class="cinema-btn text-xs font-bold gap-0.5" title="Rewind 10s (Left Arrow)">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> 10s
                  </button>

                  <!-- Forward 10s -->
                  <button onclick="player.skip(10)" class="cinema-btn text-xs font-bold gap-0.5" title="Forward 10s (Right Arrow)">
                    10s <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                  </button>

                  <!-- Volume Controls -->
                  <div class="flex items-center gap-1.5 group/vol">
                    <button onclick="player.toggleMute()" id="volBtn" class="cinema-btn" title="Mute/Unmute (M)">
                      <i id="volIcon" data-lucide="volume-2" class="w-4 h-4"></i>
                    </button>
                    <input 
                      type="range" 
                      id="volSlider" 
                      min="0" 
                      max="1" 
                      step="0.05" 
                      value="0.9" 
                      class="w-16 h-1 bg-gray-600 rounded-lg appearance-none cursor-pointer accent-brand-red opacity-80 group-hover/vol:opacity-100 transition-opacity" 
                    />
                  </div>

                  <!-- Timestamps -->
                  <div class="text-xs font-semibold text-gray-300 ml-2">
                    <span id="currentTimeDisplay">00:00:00</span>
                    <span class="text-gray-500">/</span>
                    <span id="durationDisplay">00:00:00</span>
                  </div>
                </div>

                <!-- Right Buttons (Quality, Audio, Speed, Fullscreen) -->
                <div class="flex items-center gap-2">
                  
                  <!-- Audio Tracks Dropdown -->
                  <div class="relative">
                    <button onclick="toggleMenu('audioMenuPopup')" class="cinema-pill-btn">
                      <i data-lucide="volume-2" class="w-3.5 h-3.5 text-brand-cyan"></i>
                      <span id="activeAudioLabel">Audio</span>
                      <i data-lucide="chevron-up" class="w-3 h-3 text-gray-400"></i>
                    </button>
                    <div id="audioMenuPopup" class="cinema-popup-menu w-44 hidden">
                      <div class="text-[10px] uppercase font-bold text-gray-400 px-2 py-1">Select Audio Track</div>
                      <div id="audioMenuOptions" class="space-y-0.5"></div>
                    </div>
                  </div>

                  <!-- Quality Selector Dropdown -->
                  <div class="relative">
                    <button onclick="toggleMenu('qualityMenuPopup')" class="cinema-pill-btn">
                      <i data-lucide="settings" class="w-3.5 h-3.5 text-yellow-400"></i>
                      <span id="activeQualityLabel">Quality</span>
                      <i data-lucide="chevron-up" class="w-3 h-3 text-gray-400"></i>
                    </button>
                    <div id="qualityMenuPopup" class="cinema-popup-menu w-48 hidden">
                      <div class="text-[10px] uppercase font-bold text-gray-400 px-2 py-1">Select Quality</div>
                      <div id="qualityMenuOptions" class="space-y-0.5"></div>
                    </div>
                  </div>

                  <!-- Speed Selector -->
                  <div class="relative">
                    <button onclick="toggleMenu('speedMenuPopup')" class="cinema-pill-btn">
                      <span id="activeSpeedLabel">1x</span>
                    </button>
                    <div id="speedMenuPopup" class="cinema-popup-menu w-28 hidden text-xs">
                      <button onclick="player.setSpeed(0.5)" class="cinema-menu-item">0.5x</button>
                      <button onclick="player.setSpeed(0.75)" class="cinema-menu-item">0.75x</button>
                      <button onclick="player.setSpeed(1)" class="cinema-menu-item active">1x (Normal)</button>
                      <button onclick="player.setSpeed(1.25)" class="cinema-menu-item">1.25x</button>
                      <button onclick="player.setSpeed(1.5)" class="cinema-menu-item">1.5x</button>
                      <button onclick="player.setSpeed(2)" class="cinema-menu-item">2x</button>
                    </div>
                  </div>

                  <!-- Picture in Picture -->
                  <button onclick="player.togglePiP()" class="cinema-btn" title="Picture in Picture">
                    <i data-lucide="picture-in-picture-2" class="w-4 h-4"></i>
                  </button>

                  <!-- Fullscreen -->
                  <button onclick="player.toggleFullscreen()" class="cinema-btn" title="Fullscreen (F)">
                    <i id="fullscreenIcon" data-lucide="maximize" class="w-4 h-4"></i>
                  </button>

                </div>

              </div>

            </div>

          </div>

          <!-- Web Series Episodes Selector (Shown when Series detected) -->
          <div id="seriesEpisodesBox" class="hidden bg-brand-card border border-brand-border rounded-3xl p-6 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-brand-border/60">
              <div class="flex items-center gap-2">
                <i data-lucide="list-video" class="w-5 h-5 text-brand-emerald"></i>
                <h3 class="text-sm font-bold text-white uppercase tracking-wider">Web Series Episodes</h3>
              </div>
              <span id="episodesCountBadge" class="text-xs text-gray-400">0 Episodes</span>
            </div>
            <div id="episodesButtonsList" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
              <!-- Populated via JS -->
            </div>
          </div>

          <!-- Movie Synopsis & Metadata Details -->
          <div class="bg-brand-card border border-brand-border rounded-3xl p-6 sm:p-8 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-4">
              <div>
                <h1 id="pmTitle" class="text-2xl sm:text-3xl font-black text-white">Movie Title</h1>
                <p id="pmOriginalTitle" class="text-xs text-gray-400 mt-0.5">Avengers: Infinity War (2018)</p>
              </div>
              <div class="flex items-center gap-2">
                <span id="pmRatingBadge" class="flex items-center gap-1 text-sm font-bold px-3 py-1 rounded-full bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                  <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                  <span>8.4</span>
                </span>
                <span id="pmYearBadge" class="text-xs font-bold px-3 py-1 rounded-full bg-brand-border text-gray-300">2018</span>
                <span id="pmRuntimeBadge" class="text-xs font-bold px-3 py-1 rounded-full bg-brand-border text-gray-300">149 min</span>
              </div>
            </div>

            <!-- Tags & Audio Languages -->
            <div class="flex flex-wrap items-center gap-2 pt-1 border-t border-brand-border/60">
              <span class="text-xs font-semibold text-gray-400">Audio & Formats:</span>
              <div id="pmAudioBadges" class="flex flex-wrap gap-1.5">
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-brand-emerald/15 text-brand-emerald border border-brand-emerald/30 font-medium">Dual Audio {Hindi-English}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full bg-purple-500/15 text-purple-400 border border-purple-500/30 font-medium">MKV Format</span>
              </div>
            </div>

            <!-- Overview -->
            <div>
              <h3 class="text-sm font-bold text-gray-200 uppercase tracking-wider mb-2">Overview</h3>
              <p id="pmOverview" class="text-sm text-gray-300 leading-relaxed">
                Loading overview...
              </p>
            </div>

            <!-- Genres & Cast -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-300 pt-2 border-t border-brand-border/60">
              <div>
                <span class="text-gray-400 font-semibold">Genres:</span>
                <span id="pmGenres" class="ml-1 text-white">Action, Adventure, Sci-Fi</span>
              </div>
              <div>
                <span class="text-gray-400 font-semibold">Cast:</span>
                <span id="pmCast" class="ml-1 text-white">Robert Downey Jr., Chris Hemsworth</span>
              </div>
            </div>

          </div>

        </div>

        <!-- Right: VegaMovies Qualities & Drivers Column (1 Col) -->
        <div class="space-y-6">
          
          <div class="bg-brand-card border border-brand-border rounded-3xl p-6 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-brand-border/60">
              <div class="flex items-center gap-2">
                <i data-lucide="layers" class="w-5 h-5 text-brand-red"></i>
                <h2 class="text-lg font-bold text-white">Available Qualities</h2>
              </div>
              <span id="qualityCountBadge" class="text-xs font-semibold px-2 py-0.5 rounded bg-brand-border text-gray-300">0 Qualities</span>
            </div>

            <p class="text-xs text-gray-400">
              Direct high-speed driver downloads & stream links from VegaMovies. Click Play to stream instantly:
            </p>

            <!-- Qualities List -->
            <div id="qualitiesList" class="space-y-3">
              <div class="text-center py-8 text-gray-400 text-xs animate-pulse">
                Fetching qualities from VegaMovies...
              </div>
            </div>
          </div>

          <!-- Driver Mirrors Card -->
          <div class="bg-brand-card border border-brand-border rounded-3xl p-6 space-y-4">
            <div class="flex items-center gap-2 pb-3 border-b border-brand-border/60">
              <i data-lucide="hard-drive" class="w-5 h-5 text-brand-emerald"></i>
              <h2 class="text-lg font-bold text-white">All Driver Mirrors</h2>
            </div>
            <div id="driverMirrorsList" class="space-y-2 text-xs">
              <div class="p-3 rounded-xl bg-[#0e1015] border border-brand-border text-gray-400">
                Select any quality to view fast driver links (R2, G-Direct, V-Cloud, Filepress).
              </div>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- ============================================ -->
    <!-- HERO FEATURED MOVIE BANNER -->
    <!-- ============================================ -->
    <section id="heroSection" class="relative rounded-3xl overflow-hidden border border-brand-border bg-gradient-to-r from-black via-brand-dark to-transparent min-h-[420px] flex items-center shadow-2xl">
      <img 
        id="heroBackdrop" 
        src="https://image.tmdb.org/t/p/original/bxfCEo5a6t6gxUR9EjJLlqCe73i.jpg" 
        alt="Hero Backdrop" 
        class="absolute inset-0 w-full h-full object-cover opacity-35 object-center mix-blend-luminosity filter blur-[0.5px]"
      />
      <div class="absolute inset-0 bg-gradient-to-t from-[#0a0b0e] via-[#0a0b0e]/70 to-transparent"></div>
      <div class="absolute inset-0 bg-gradient-to-r from-[#0a0b0e] via-[#0a0b0e]/90 to-transparent"></div>

      <div class="relative z-10 max-w-2xl p-8 sm:p-12 space-y-4">
        <div class="flex items-center gap-2">
          <span class="text-xs font-bold uppercase tracking-widest text-brand-emerald bg-brand-emerald/10 border border-brand-emerald/30 px-3 py-1 rounded-full">
            Featured Stream
          </span>
          <span id="heroRating" class="text-xs font-bold flex items-center gap-1 text-yellow-400 bg-black/60 px-2.5 py-1 rounded-full border border-yellow-500/30">
            <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i> 8.4
          </span>
        </div>

        <h1 id="heroTitle" class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
          Avengers: Infinity War (2018)
        </h1>

        <p id="heroOverview" class="text-sm text-gray-300 line-clamp-3 leading-relaxed">
          As the Avengers and their allies have continued to protect the world from threats too large for any one hero to handle, a new danger has emerged from the cosmic shadows: Thanos.
        </p>

        <div class="pt-2 flex flex-wrap items-center gap-4">
          <button id="heroPlayBtn" onclick="playHeroMovie()" class="flex items-center gap-2 px-6 py-3 rounded-2xl bg-brand-red hover:bg-brand-hoverRed text-white font-bold text-sm shadow-xl shadow-brand-red/40 hover:scale-105 transition-all">
            <i data-lucide="play" class="w-4 h-4 fill-current"></i>
            Watch on VegaMovies
          </button>
          <button id="heroDetailsBtn" onclick="openHeroDetails()" class="flex items-center gap-2 px-5 py-3 rounded-2xl bg-brand-card hover:bg-brand-cardHover text-gray-200 border border-brand-border font-semibold text-sm transition-all">
            <i data-lucide="info" class="w-4 h-4"></i>
            Movie Details
          </button>
        </div>
      </div>
    </section>

    <!-- ============================================ -->
    <!-- CATEGORY FILTERS & MOVIE CATALOG -->
    <!-- ============================================ -->
    <section class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-4 pb-2 border-b border-brand-border">
        <div class="flex items-center gap-3">
          <i data-lucide="film" class="w-6 h-6 text-brand-red"></i>
          <h2 id="catalogTitle" class="text-2xl font-black text-white">Trending Movies</h2>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <button onclick="loadCategory('trending')" id="tabTrending" class="cat-tab px-4 py-2 rounded-xl text-xs font-bold bg-brand-red text-white transition-all shadow-md">
            🔥 Trending Now
          </button>
          <button onclick="loadCategory('bollywood')" id="tabBollywood" class="cat-tab px-4 py-2 rounded-xl text-xs font-medium bg-brand-card text-gray-300 hover:text-white border border-brand-border transition-all">
            🇮🇳 Bollywood Hits
          </button>
          <button onclick="loadCategory('popular')" id="tabPopular" class="cat-tab px-4 py-2 rounded-xl text-xs font-medium bg-brand-card text-gray-300 hover:text-white border border-brand-border transition-all">
            🌍 Hollywood / Dual Audio
          </button>
        </div>
      </div>

      <div id="movieGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
        <div class="col-span-full py-16 text-center text-gray-400">
          <div class="w-10 h-10 border-4 border-brand-red border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
          <p class="text-sm">Loading movies from TMDB...</p>
        </div>
      </div>
    </section>

  </main>

  <!-- ============================================== -->
  <!-- FOOTER -->
  <!-- ============================================== -->
  <footer class="border-t border-brand-border/60 py-8 bg-[#07080a] text-center text-xs text-gray-500">
    <div class="max-w-7xl mx-auto px-4 space-y-2">
      <div class="flex items-center justify-center gap-2 text-white font-bold text-sm">
        <span class="text-brand-red font-black">FLIXFLIX</span> Streaming Engine
      </div>
      <p>Direct driver stream & multi-audio extraction powered by VegaMovies.</p>
    </div>
  </footer>

  <!-- Dedicated Cinema Player Script -->
  <script src="js/player.js"></script>

  <!-- ============================================== -->
  <!-- JAVASCRIPT APPLICATION LOGIC -->
  <!-- ============================================== -->
  <script>
    const API_BASE = 'api.php?endpoint=';
    let currentScrapedData = null;
    let currentHeroMovie = null;
    let activeCategory = 'trending';
    let searchDebounceTimer = null;

    const TMDB_IMG_POSTER = 'https://image.tmdb.org/t/p/w500';
    const TMDB_IMG_BACKDROP = 'https://image.tmdb.org/t/p/original';

    document.addEventListener('DOMContentLoaded', () => {
      lucide.createIcons();
      initSearchInput();
      
      // Initialize standalone player
      player.init(
        document.getElementById('mainVideo'),
        document.getElementById('playerContainer')
      );

      loadCategory('trending');
    });

    function toggleMenu(menuId) {
      const el = document.getElementById(menuId);
      if (!el) return;
      const isHidden = el.classList.contains('hidden');
      player.closeAllMenus();
      if (isHidden) el.classList.remove('hidden');
    }

    // ----------------------------------------------------
    // SEARCH & AUTOCOMPLETE
    // ----------------------------------------------------
    function initSearchInput() {
      const searchInput = document.getElementById('searchInput');
      const searchDropdown = document.getElementById('searchDropdown');
      const clearSearchBtn = document.getElementById('clearSearchBtn');

      searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        
        if (query.length > 0) {
          clearSearchBtn.classList.remove('hidden');
        } else {
          clearSearchBtn.classList.add('hidden');
          searchDropdown.classList.add('hidden');
          return;
        }

        if (/vegamovies\./i.test(query)) {
          showDirectUrlSuggestion(query);
          return;
        }

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
          fetchLiveSuggestions(query);
        }, 280);
      });

      document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
          searchDropdown.classList.add('hidden');
        }
      });
    }

    function clearSearch() {
      const searchInput = document.getElementById('searchInput');
      searchInput.value = '';
      document.getElementById('clearSearchBtn').classList.add('hidden');
      document.getElementById('searchDropdown').classList.add('hidden');
    }

    function showDirectUrlSuggestion(url) {
      const searchDropdown = document.getElementById('searchDropdown');
      const container = document.getElementById('searchDropdownContent');
      searchDropdown.classList.remove('hidden');

      container.innerHTML = `
        <div onclick="playDirectVegaUrl('${url}')" class="p-3 bg-brand-emerald/10 border border-brand-emerald/30 rounded-xl cursor-pointer hover:bg-brand-emerald/20 transition-all flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-brand-emerald text-white flex items-center justify-center flex-shrink-0">
            <i data-lucide="play" class="w-5 h-5 fill-current"></i>
          </div>
          <div>
            <div class="text-sm font-bold text-white">Direct VegaMovies Link Detected</div>
            <div class="text-xs text-gray-400 truncate max-w-md">${url}</div>
          </div>
        </div>
      `;
      lucide.createIcons();
    }

    async function fetchLiveSuggestions(query) {
      const searchDropdown = document.getElementById('searchDropdown');
      const container = document.getElementById('searchDropdownContent');

      try {
        const [tmdbRes, vegaRes] = await Promise.allSettled([
          fetch(`${API_BASE}tmdb/search?q=${encodeURIComponent(query)}`),
          fetch(`${API_BASE}vegamovies/search?q=${encodeURIComponent(query)}`)
        ]);

        let tmdbResults = [];
        if (tmdbRes.status === 'fulfilled') {
          const tmdbData = await tmdbRes.value.json();
          tmdbResults = (tmdbData.results || []).slice(0, 5);
        }

        let vegaResults = [];
        if (vegaRes.status === 'fulfilled') {
          const vegaData = await vegaRes.value.json();
          vegaResults = (vegaData.hits || []).slice(0, 5);
        }

        if (tmdbResults.length === 0 && vegaResults.length === 0) {
          container.innerHTML = `
            <div class="p-6 text-center text-xs text-gray-400">
              <i data-lucide="search-x" class="w-6 h-6 mx-auto mb-2 text-gray-500"></i>
              No results found for "${query}"
            </div>
          `;
          searchDropdown.classList.remove('hidden');
          lucide.createIcons();
          return;
        }

        let html = '';

        // 1. VEGAMOVIES DIRECT RELEASES (Instant 1-Click Play)
        if (vegaResults.length > 0) {
          html += `
            <div class="flex items-center justify-between px-3 pt-2.5 pb-1 text-[11px] font-bold text-brand-emerald uppercase tracking-wider border-b border-brand-border/40">
              <span class="flex items-center gap-1.5"><i data-lucide="zap" class="w-3.5 h-3.5 fill-current"></i> VegaMovies Releases (Instant Play)</span>
              <span class="text-[10px] text-gray-400 font-normal">Direct Stream</span>
            </div>
            <div class="space-y-1 p-1">
          `;
          html += vegaResults.map(v => {
            const thumb = v.thumbnail || 'https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg';
            return `
              <div onclick="playDirectVegaUrl('${v.fullUrl}')" class="flex items-center gap-3 p-2 rounded-xl hover:bg-brand-emerald/15 cursor-pointer transition-all group">
                <img src="${thumb}" alt="${escapeJs(v.title)}" class="w-11 h-14 object-cover rounded-lg flex-shrink-0 border border-brand-border" />
                <div class="flex-1 min-w-0">
                  <div class="text-xs font-bold text-white group-hover:text-brand-emerald line-clamp-1 leading-snug">${v.title}</div>
                  <div class="flex items-center gap-2 text-[11px] text-gray-400 mt-1">
                    <span class="px-1.5 py-0.2 rounded bg-brand-emerald/20 text-brand-emerald text-[10px] font-semibold">VEGA</span>
                    <span>Direct Drivers & Multi-Audio</span>
                  </div>
                </div>
                <button onclick="event.stopPropagation(); playDirectVegaUrl('${v.fullUrl}')" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-brand-emerald hover:bg-emerald-600 text-white flex items-center gap-1.5 shadow-md shadow-brand-emerald/20 transition-all flex-shrink-0">
                  <i data-lucide="play" class="w-3 h-3 fill-current"></i>
                  <span>Watch</span>
                </button>
              </div>
            `;
          }).join('');
          html += `</div>`;
        }

        // 2. TMDB MOVIES & WEB SERIES
        if (tmdbResults.length > 0) {
          html += `
            <div class="flex items-center justify-between px-3 pt-3 pb-1 text-[11px] font-bold text-brand-cyan uppercase tracking-wider border-b border-brand-border/40">
              <span class="flex items-center gap-1.5"><i data-lucide="film" class="w-3.5 h-3.5 text-brand-red"></i> TMDB Cinema & Series</span>
              <span class="text-[10px] text-gray-400 font-normal">Match Vega Release</span>
            </div>
            <div class="space-y-1 p-1">
          `;
          html += tmdbResults.map(m => {
            const poster = m.poster_path ? `${TMDB_IMG_POSTER}${m.poster_path}` : 'https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg';
            const year = m.release_date ? m.release_date.split('-')[0] : 'N/A';
            const rating = m.vote_average ? Number(m.vote_average).toFixed(1) : 'NR';
            const isTv = m.media_type === 'tv';
            const typeBadge = isTv ? 'bg-purple-500/20 text-purple-400 border-purple-500/30' : 'bg-blue-500/20 text-blue-400 border-blue-500/30';
            const typeLabel = isTv ? 'SERIES' : 'MOVIE';

            return `
              <div onclick="selectMovie(${m.id}, '${m.media_type || 'movie'}', '${escapeJs(m.title)}')" class="flex items-center gap-3 p-2 rounded-xl hover:bg-brand-border/60 cursor-pointer transition-all group">
                <img src="${poster}" alt="${escapeJs(m.title)}" class="w-11 h-14 object-cover rounded-lg flex-shrink-0 border border-brand-border" />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <span class="text-[10px] font-extrabold px-1.5 py-0.2 rounded border ${typeBadge}">${typeLabel}</span>
                    <span class="text-xs font-bold text-white group-hover:text-brand-red truncate">${m.title}</span>
                  </div>
                  <div class="flex items-center gap-2 text-[11px] text-gray-400 mt-1">
                    <span>${year}</span>
                    <span>•</span>
                    <span class="flex items-center gap-0.5 text-yellow-400 font-semibold">
                      <i data-lucide="star" class="w-3 h-3 fill-current"></i> ${rating}
                    </span>
                  </div>
                </div>
                <button onclick="event.stopPropagation(); selectMovie(${m.id}, '${m.media_type || 'movie'}', '${escapeJs(m.title)}')" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-brand-border text-gray-300 group-hover:bg-brand-red group-hover:text-white transition-all flex items-center gap-1.5 flex-shrink-0">
                  <i data-lucide="search" class="w-3 h-3"></i>
                  <span>Stream</span>
                </button>
              </div>
            `;
          }).join('');
          html += `</div>`;
        }

        container.innerHTML = html;
        searchDropdown.classList.remove('hidden');
        lucide.createIcons();
      } catch (err) {
        console.error('Search error:', err);
      }
    }

    // ----------------------------------------------------
    // LOAD CATEGORIES & MOVIE GRID
    // ----------------------------------------------------
    async function loadCategory(cat) {
      activeCategory = cat;
      const grid = document.getElementById('movieGrid');
      const title = document.getElementById('catalogTitle');

      ['tabTrending', 'tabBollywood', 'tabPopular'].forEach(tabId => {
        const el = document.getElementById(tabId);
        if (el) {
          el.className = 'cat-tab px-4 py-2 rounded-xl text-xs font-medium bg-brand-card text-gray-300 hover:text-white border border-brand-border transition-all';
        }
      });

      let endpoint = API_BASE + 'tmdb/trending';
      if (cat === 'trending') {
        document.getElementById('tabTrending').className = 'cat-tab px-4 py-2 rounded-xl text-xs font-bold bg-brand-red text-white transition-all shadow-md';
        title.innerText = 'Trending Movies Today';
        endpoint = API_BASE + 'tmdb/trending';
      } else if (cat === 'bollywood') {
        document.getElementById('tabBollywood').className = 'cat-tab px-4 py-2 rounded-xl text-xs font-bold bg-brand-red text-white transition-all shadow-md';
        title.innerText = 'Popular Bollywood & Hindi Cinema';
        endpoint = API_BASE + 'tmdb/bollywood';
      } else if (cat === 'popular') {
        document.getElementById('tabPopular').className = 'cat-tab px-4 py-2 rounded-xl text-xs font-bold bg-brand-red text-white transition-all shadow-md';
        title.innerText = 'Hollywood & Global Hits';
        endpoint = API_BASE + 'tmdb/popular';
      }

      grid.innerHTML = `
        <div class="col-span-full py-16 text-center text-gray-400">
          <div class="w-10 h-10 border-4 border-brand-red border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
          <p class="text-sm">Loading movies from TMDB...</p>
        </div>
      `;

      try {
        const res = await fetch(endpoint);
        const data = await res.json();
        const movies = data.results || [];

        if (movies.length > 0 && (!currentHeroMovie || cat === 'trending')) {
          setHeroMovie(movies[0]);
        }

        renderMovieGrid(movies);
      } catch (err) {
        grid.innerHTML = `<div class="col-span-full py-10 text-center text-red-400">Failed to load movies: ${err.message}</div>`;
      }
    }

    function setHeroMovie(m) {
      currentHeroMovie = m;
      document.getElementById('heroTitle').innerText = m.title;
      document.getElementById('heroOverview').innerText = m.overview || 'Stream and download full movie on VegaMovies with multi-audio support.';
      document.getElementById('heroRating').innerHTML = `<i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i> ${(m.vote_average || 8.0).toFixed(1)}`;
      if (m.backdrop_path) {
        document.getElementById('heroBackdrop').src = `${TMDB_IMG_BACKDROP}${m.backdrop_path}`;
      }
      lucide.createIcons();
    }

    function playHeroMovie() {
      if (currentHeroMovie) {
        selectMovie(currentHeroMovie.id, currentHeroMovie.media_type || 'movie', currentHeroMovie.title);
      } else {
        playDirectVegaUrl('https://new1.vegamovies.lease/download-avengers-infinity-war-2018-hindi-dubbed-480p-720p-1080p-2160p-4k/');
      }
    }

    function openHeroDetails() {
      if (currentHeroMovie) selectMovie(currentHeroMovie.id, currentHeroMovie.media_type || 'movie', currentHeroMovie.title);
    }

    function renderMovieGrid(movies) {
      const grid = document.getElementById('movieGrid');
      if (movies.length === 0) {
        grid.innerHTML = '<div class="col-span-full py-10 text-center text-gray-400">No movies found.</div>';
        return;
      }

      grid.innerHTML = movies.map(m => {
        const poster = m.poster_path ? `${TMDB_IMG_POSTER}${m.poster_path}` : 'https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg';
        const year = m.release_date ? m.release_date.split('-')[0] : 'N/A';
        const rating = m.vote_average ? Number(m.vote_average).toFixed(1) : 'NR';
        const isTv = m.media_type === 'tv' || (!m.title && m.name);
        const title = m.title || m.name || 'Untitled';
        const mType = isTv ? 'tv' : 'movie';

        return `
          <div onclick="selectMovie(${m.id}, '${mType}', '${escapeJs(title)}')" class="poster-hover group cursor-pointer flex flex-col bg-brand-card rounded-2xl overflow-hidden border border-brand-border hover:border-brand-emerald/60 transition-all duration-300 hover:shadow-xl hover:shadow-brand-emerald/10">
            <div class="relative aspect-[2/3] overflow-hidden bg-black/60">
              <img src="${poster}" alt="${escapeJs(title)}" loading="lazy" class="w-full h-full object-cover transition-transform duration-500" />
              <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <div class="w-12 h-12 rounded-full bg-brand-emerald text-white flex items-center justify-center shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform">
                  <i data-lucide="play" class="w-6 h-6 fill-current ml-0.5"></i>
                </div>
              </div>
              <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-md bg-black/75 backdrop-blur-md text-[10px] font-extrabold uppercase tracking-wider text-brand-cyan border border-brand-cyan/30">
                ${isTv ? 'SERIES' : 'MOVIE'}
              </div>
              <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-md bg-black/70 backdrop-blur-md text-[11px] font-bold text-yellow-400 flex items-center gap-1 border border-yellow-500/20">
                <i data-lucide="star" class="w-3 h-3 fill-current"></i> ${rating}
              </div>
            </div>
            <div class="p-3 flex-1 flex flex-col justify-between">
              <h3 class="text-xs font-bold text-white group-hover:text-brand-emerald transition-colors line-clamp-1" title="${escapeJs(title)}">${title}</h3>
              <div class="flex items-center justify-between text-[11px] text-gray-400 mt-1">
                <span>${year}</span>
                <span class="text-brand-emerald font-medium">VegaMovies</span>
              </div>
            </div>
          </div>
        `;
      }).join('');

      lucide.createIcons();
    }

    // ----------------------------------------------------
    // SELECT MOVIE/SERIES & VEGAMOVIES RESOLUTION
    // ----------------------------------------------------
    async function selectMovie(tmdbId, mediaType = 'movie', explicitTitle = '') {
      document.getElementById('searchDropdown').classList.add('hidden');
      showSearchToast(`Searching VegaMovies for ${explicitTitle || 'title'}...`);

      try {
        let title = explicitTitle;
        let releaseYear = '';

        try {
          const tmdbRes = await fetch(`${API_BASE}tmdb/${mediaType}/${tmdbId}`);
          if (tmdbRes.ok) {
            const item = await tmdbRes.json();
            title = item.title || item.name || title;
            releaseYear = (item.release_date || item.first_air_date || '').split('-')[0] || '';
          }
        } catch (e) {
          console.warn('TMDB details fetch fallback:', e);
        }

        if (!title) {
          hideSearchToast();
          alert('Could not determine movie or series title.');
          return;
        }

        // Multi-tier VegaMovies Search
        let hits = [];
        const cleanTitle = title.replace(/[:\-']/g, ' ').replace(/\s+/g, ' ').trim();
        
        // Tier 1: Clean Title
        showSearchToast(`Searching VegaMovies for "${cleanTitle}"...`);
        const res1 = await fetch(`${API_BASE}vegamovies/search?q=${encodeURIComponent(cleanTitle)}`);
        if (res1.ok) {
          const d1 = await res1.json();
          hits = d1.hits || [];
        }

        // Tier 2: First 2 Words (if multi-word and no hits)
        if (hits.length === 0) {
          const words = cleanTitle.split(/\s+/).filter(w => w.length > 1);
          if (words.length > 2) {
            const q2 = words.slice(0, 2).join(' ');
            showSearchToast(`Searching VegaMovies for "${q2}"...`);
            const res2 = await fetch(`${API_BASE}vegamovies/search?q=${encodeURIComponent(q2)}`);
            if (res2.ok) {
              const d2 = await res2.json();
              hits = d2.hits || [];
            }
          }
        }

        // Tier 3: First Word
        if (hits.length === 0) {
          const words = cleanTitle.split(/\s+/).filter(w => w.length > 2);
          if (words.length > 0) {
            const firstWord = words[0];
            showSearchToast(`Searching VegaMovies for "${firstWord}"...`);
            const res3 = await fetch(`${API_BASE}vegamovies/search?q=${encodeURIComponent(firstWord)}`);
            if (res3.ok) {
              const d3 = await res3.json();
              hits = d3.hits || [];
            }
          }
        }

        hideSearchToast();

        if (hits.length === 1) {
          window.location.href = `watch.php?url=${encodeURIComponent(hits[0].fullUrl)}`;
        } else if (hits.length > 1) {
          showVegaVersionsModal(title, hits);
        } else {
          alert(`Movie/Series "${title}" not found on VegaMovies.\nYou can search with different keywords or paste a direct VegaMovies link!`);
        }

      } catch (err) {
        hideSearchToast();
        alert('Error connecting to VegaMovies: ' + err.message);
      }
    }

    // ----------------------------------------------------
    // VEGAMOVIES VERSION SELECTION MODAL
    // ----------------------------------------------------
    function showVegaVersionsModal(title, hits) {
      const modal = document.getElementById('vegaVersionsModal');
      const modalTitle = document.getElementById('vegaModalTitle');
      const modalSubtitle = document.getElementById('vegaModalSubtitle');
      const list = document.getElementById('vegaVersionsList');

      modalTitle.innerText = `Select Release: ${title}`;
      modalSubtitle.innerText = `${hits.length} VegaMovies releases/seasons found. Pick your preferred version:`;

      list.innerHTML = hits.map(hit => {
        const thumb = hit.thumbnail || 'https://upload.wikimedia.org/wikipedia/commons/6/65/No-Image-Placeholder.svg';
        let tags = [];
        if (/season\s*\d+/i.test(hit.title)) {
          const sMatch = hit.title.match(/season\s*\d+(?:\s*[-–]\s*\d+)?/i);
          if (sMatch) tags.push(`<span class="px-2 py-0.5 rounded bg-brand-cyan/20 text-brand-cyan border border-brand-cyan/30 text-[10px] font-bold uppercase">${sMatch[0]}</span>`);
        }
        if (/hindi|dual\s*audio/i.test(hit.title)) {
          tags.push(`<span class="px-2 py-0.5 rounded bg-brand-emerald/20 text-brand-emerald border border-brand-emerald/30 text-[10px] font-bold uppercase">Dual Audio</span>`);
        }
        if (/4k|2160p/i.test(hit.title)) {
          tags.push(`<span class="px-2 py-0.5 rounded bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 text-[10px] font-bold">4K</span>`);
        } else if (/1080p/i.test(hit.title)) {
          tags.push(`<span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-400 border border-purple-500/30 text-[10px] font-bold">1080p</span>`);
        } else if (/720p/i.test(hit.title)) {
          tags.push(`<span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-400 border border-blue-500/30 text-[10px] font-bold">720p</span>`);
        }

        return `
          <div class="p-3.5 bg-[#0e1015] border border-brand-border hover:border-brand-emerald/60 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 transition-all">
            <div class="flex items-center gap-3 min-w-0">
              <img src="${thumb}" alt="${escapeJs(hit.title)}" class="w-12 h-16 object-cover rounded-xl flex-shrink-0 border border-brand-border/60" />
              <div class="min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-white line-clamp-2 leading-snug">${hit.title}</h4>
                <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                  ${tags.join(' ')}
                  <span class="text-[10px] text-gray-400 font-medium">VegaMovies Direct</span>
                </div>
              </div>
            </div>
            <button onclick="playDirectVegaUrl('${hit.fullUrl}')" class="w-full sm:w-auto flex-shrink-0 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-emerald hover:bg-emerald-600 text-white font-bold text-xs shadow-lg shadow-brand-emerald/20 transition-all">
              <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i>
              <span>Stream Now</span>
            </button>
          </div>
        `;
      }).join('');

      modal.classList.remove('hidden');
      lucide.createIcons();
    }

    function closeVegaVersionsModal() {
      const modal = document.getElementById('vegaVersionsModal');
      if (modal) modal.classList.add('hidden');
    }

    function showSearchToast(text) {
      const toast = document.getElementById('searchToast');
      const span = document.getElementById('searchToastText');
      if (toast && span) {
        span.innerText = text;
        toast.classList.remove('hidden');
      }
    }

    function hideSearchToast() {
      const toast = document.getElementById('searchToast');
      if (toast) toast.classList.add('hidden');
    }

    function escapeJs(str) {
      return (str || '').replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    // ----------------------------------------------------
    // SCRAPE & PLAY FROM VEGAMOVIES
    // ----------------------------------------------------
    async function scrapeAndPlayVega(postUrl) {
      window.location.href = `watch.php?url=${encodeURIComponent(postUrl)}`;
    }

    function playDirectVegaUrl(url) {
      closeDirectUrlModal();
      window.location.href = `watch.php?url=${encodeURIComponent(url)}`;
    }

    function submitDirectUrl() {
      const url = document.getElementById('modalDirectUrlInput').value.trim();
      if (url) playDirectVegaUrl(url);
    }

    // ----------------------------------------------------
    // LOAD STREAM INTO DEDICATED PLAYER WITH ALL SERVERS
    // ----------------------------------------------------
    async function loadQualityStream(nexdriveUrl, qualityLabel) {
      player.showSpinner(`Resolving driver mirrors for ${qualityLabel}...`);
      document.getElementById('activeQualityLabel').innerText = qualityLabel;
      document.getElementById('playerActiveQualityBadge').innerText = qualityLabel;

      try {
        const res = await fetch(`${API_BASE}vegamovies/resolve-nexdrive?url=${encodeURIComponent(nexdriveUrl)}`);
        const data = await res.json();
        console.log('[Nexdrive Resolved]:', data);

        renderDriverMirrors(data.servers || []);

        if (data.episodes && data.episodes.length > 0) {
          renderSeriesEpisodes(data.episodes);
        }

        // Set all servers in player (User can switch between them anytime!)
        if (data.servers && data.servers.length > 0) {
          player.setServers(data.servers);
        } else if (data.primaryStreamUrl) {
          player.loadSource(data.primaryStreamUrl);
        } else {
          player.hideSpinner();
          alert('Stream source not directly available. Use driver links to download.');
        }

      } catch (err) {
        player.hideSpinner();
        alert('Could not resolve stream: ' + err.message);
      }
    }

    function renderQualities(qualities) {
      const list = document.getElementById('qualitiesList');
      const badge = document.getElementById('qualityCountBadge');
      badge.innerText = `${qualities.length} Qualities Available`;

      if (qualities.length === 0) {
        list.innerHTML = `<div class="text-xs text-gray-400 text-center py-4">No download links parsed.</div>`;
        return;
      }

      list.innerHTML = qualities.map((q, idx) => {
        let qualBadgeColor = 'bg-blue-500/20 text-blue-400 border-blue-500/30';
        if (q.quality === '1080p') qualBadgeColor = 'bg-purple-500/20 text-purple-400 border-purple-500/30';
        if (q.quality === '4K 2160p') qualBadgeColor = 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30';
        if (q.quality === '480p') qualBadgeColor = 'bg-gray-500/20 text-gray-400 border-gray-500/30';

        return `
          <div class="p-3.5 bg-[#0e1015] border border-brand-border hover:border-gray-600 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 transition-all">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold px-2 py-0.5 rounded border ${qualBadgeColor}">${q.quality}</span>
                ${q.isHevc ? '<span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">HEVC</span>' : ''}
                ${q.is60Fps ? '<span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">60FPS</span>' : ''}
                <span class="text-xs font-bold text-white">${q.size || 'Full File'}</span>
              </div>
              <div class="text-[11px] text-gray-400 line-clamp-1 truncate max-w-xs" title="${q.heading}">${q.heading}</div>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
              <button onclick="loadQualityStream('${q.nexdriveUrl}', '${q.quality}')" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-red hover:bg-brand-hoverRed text-white text-xs font-bold transition-all shadow-md">
                <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i>
                Play
              </button>
              <a href="${q.nexdriveUrl}" target="_blank" rel="noopener noreferrer" class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-border hover:bg-gray-700 text-gray-200 text-xs font-semibold transition-all">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                Driver
              </a>
            </div>
          </div>
        `;
      }).join('');

      lucide.createIcons();
    }

    function renderDriverMirrors(servers) {
      const container = document.getElementById('driverMirrorsList');
      if (!servers || servers.length === 0) {
        container.innerHTML = '<div class="text-gray-400 p-2">Select a quality to load mirrors.</div>';
        return;
      }

      container.innerHTML = servers.map(s => {
        return `
          <a href="${s.url}" target="_blank" download class="flex items-center justify-between p-2.5 rounded-xl bg-[#0e1015] border border-brand-border text-white hover:border-gray-500 transition-all">
            <div class="flex items-center gap-2">
              <i data-lucide="hard-drive" class="w-4 h-4 text-brand-emerald"></i>
              <span class="font-bold">${s.name}</span>
            </div>
            <span class="text-[10px] uppercase font-bold text-brand-cyan">${s.badge || 'Download'}</span>
          </a>
        `;
      }).join('');

      lucide.createIcons();
    }

    function renderSeriesEpisodes(episodes) {
      const container = document.getElementById('episodesButtonsList');
      const badge = document.getElementById('episodesCountBadge');
      badge.innerText = `${episodes.length} Episodes Available`;

      container.innerHTML = episodes.map((ep, idx) => {
        const isFirst = idx === 0;
        return `
          <button onclick="playEpisodeDirect('${ep.fastdlUrl}', '${ep.episodeTitle}', ${idx})" id="epBtn_${idx}" class="p-2.5 rounded-xl border ${isFirst ? 'bg-brand-red text-white font-bold border-brand-red' : 'bg-[#0e1015] text-gray-300 border-brand-border hover:border-gray-600'} text-xs text-center transition-all">
            ${ep.episodeTitle}
          </button>
        `;
      }).join('');
    }

    async function playEpisodeDirect(fastdlUrl, title, idx) {
      player.showSpinner(`Loading ${title}...`);
      
      document.querySelectorAll('#episodesButtonsList button').forEach((btn, i) => {
        if (i === idx) {
          btn.className = 'p-2.5 rounded-xl border bg-brand-red text-white font-bold border-brand-red text-xs text-center transition-all';
        } else {
          btn.className = 'p-2.5 rounded-xl border bg-[#0e1015] text-gray-300 border-brand-border hover:border-gray-600 text-xs text-center transition-all';
        }
      });

      try {
        const res = await fetch(`${API_BASE}vegamovies/resolve-nexdrive?url=${encodeURIComponent(fastdlUrl)}`);
        const data = await res.json();
        if (data.servers && data.servers.length > 0) {
          player.setServers(data.servers);
        } else if (data.primaryStreamUrl) {
          player.loadSource(data.primaryStreamUrl);
        }
      } catch (e) {
        player.hideSpinner();
        alert('Episode stream load error: ' + e.message);
      }
    }

    function closePlayer() {
      if (player && player.video) {
        player.video.pause();
        player.video.src = '';
      }
      document.getElementById('playerSection').classList.add('hidden');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function goHome(e) {
      if (e) e.preventDefault();
      closePlayer();
      clearSearch();
      loadCategory('trending');
    }

    function openDirectUrlModal() {
      document.getElementById('urlModal').classList.remove('hidden');
    }

    function closeDirectUrlModal() {
      document.getElementById('urlModal').classList.add('hidden');
    }
  </script>

</body>
</html>

