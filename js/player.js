/**
 * FLIXFLIX Dedicated Cinema Video Player Engine
 * High-performance HTML5 Video player with seamless HTTP 206 seeking,
 * multi-server mirror selection, quality switching, and multi-audio support.
 */

class CinemaPlayer {
  constructor() {
    this.video = null;
    this.container = null;
    this.servers = [];
    this.qualities = [];
    this.audioTracks = [];
    this.currentServerIndex = 0;
    this.currentQualityIndex = 0;
    this.currentAudioIndex = 0;
    this.isSeeking = false;
    this.hideControlsTimeout = null;
    this.savedPlaybackTime = 0;
    this.onStateChangeCallback = null;
    this.isEventsBound = false;
  }

  init(videoEl, containerEl) {
    this.video = videoEl || document.getElementById('mainVideo');
    this.container = containerEl || document.getElementById('playerContainer');

    if (this.video && this.container && !this.isEventsBound) {
      this.bindEvents();
      this.isEventsBound = true;
      console.log('[CinemaPlayer] Initialized successfully with DOM elements.');
    }
  }

  ensureInitialized() {
    if (!this.video || !this.container) {
      const v = document.getElementById('mainVideo');
      const c = document.getElementById('playerContainer');
      if (v && c) {
        this.init(v, c);
      }
    }
    return !!this.video;
  }

  bindEvents() {
    const v = this.video;
    if (!v) return;

    // Video Play / Pause / Load Events
    v.addEventListener('play', () => {
      this.updatePlayState(true);
      this.resetControlsTimeout();
    });

    v.addEventListener('pause', () => {
      this.updatePlayState(false);
      this.showControls();
    });

    v.addEventListener('loadedmetadata', () => {
      console.log(`[CinemaPlayer] Metadata loaded. Duration: ${v.duration}s`);
      this.updateDurationDisplay();
      this.hideSpinner();

      // Resume from previous timestamp if switching quality or server
      if (this.savedPlaybackTime > 0 && this.savedPlaybackTime < v.duration) {
        console.log(`[CinemaPlayer] Resuming playback from ${this.savedPlaybackTime.toFixed(1)}s`);
        v.currentTime = this.savedPlaybackTime;
        this.savedPlaybackTime = 0;
      }

      // Detect native HTML5 audio tracks if exposed by container
      if (v.audioTracks && v.audioTracks.length > 0) {
        const detected = [];
        for (let i = 0; i < v.audioTracks.length; i++) {
          const t = v.audioTracks[i];
          const name = t.label || t.language || `Track ${i + 1}`;
          detected.push(name);
        }
        console.log('[CinemaPlayer] Auto-detected HTML5 audio tracks:', detected);
        this.setAudioTracks(detected);
      }

      v.play().catch(e => console.log('[CinemaPlayer] Auto-play notice:', e.message));
    });

    v.addEventListener('timeupdate', () => {
      if (!this.isSeeking && v.duration) {
        const pct = (v.currentTime / v.duration) * 100;
        this.updateTimelineProgress(pct, v.currentTime);
      }
    });

    v.addEventListener('progress', () => {
      if (v.buffered.length > 0 && v.duration) {
        const end = v.buffered.end(v.buffered.length - 1);
        const pct = (end / v.duration) * 100;
        const bufferBar = document.getElementById('playerBufferBar');
        if (bufferBar) bufferBar.style.width = `${pct}%`;
      }
    });

    v.addEventListener('waiting', () => {
      this.showSpinner('Buffering stream...');
    });

    v.addEventListener('playing', () => {
      this.hideSpinner();
    });

    v.addEventListener('error', (e) => {
      console.error('[CinemaPlayer Error]:', v.error);
      this.handleStreamError();
    });

    // Scrubber / Timeline Slider Events
    const slider = document.getElementById('timelineSlider');
    if (slider) {
      slider.addEventListener('input', (e) => {
        this.isSeeking = true;
        const pct = parseFloat(e.target.value);
        const progressBar = document.getElementById('playerProgressBar');
        if (progressBar) progressBar.style.width = `${pct}%`;

        if (v.duration) {
          const seekTime = (pct / 100) * v.duration;
          const currDisplay = document.getElementById('currentTimeDisplay');
          if (currDisplay) currDisplay.innerText = this.formatTime(seekTime);
        }
      });

      slider.addEventListener('change', (e) => {
        if (v.duration) {
          const pct = parseFloat(e.target.value);
          const seekTime = (pct / 100) * v.duration;
          console.log(`[CinemaPlayer Seek] Seeking to: ${seekTime.toFixed(1)}s (${this.formatTime(seekTime)})`);
          v.currentTime = seekTime;
        }
        this.isSeeking = false;
      });
    }

    // Volume Slider
    const volSlider = document.getElementById('volSlider');
    if (volSlider) {
      volSlider.addEventListener('input', (e) => {
        const val = parseFloat(e.target.value);
        v.volume = val;
        v.muted = (val === 0);
        this.updateVolumeUI();
      });
    }

    // Container interactions & auto-hide
    this.container.addEventListener('mousemove', () => {
      this.showControls();
      this.resetControlsTimeout();
    });

    this.container.addEventListener('mouseleave', () => {
      if (!v.paused) this.hideControls();
    });

    // Keyboard Shortcuts
    document.addEventListener('keydown', (e) => this.handleKeyboard(e));
  }

  // Load a direct video stream without proxy
  loadSource(url) {
    if (!url) return;
    this.ensureInitialized();
    if (!this.video) {
      console.warn('[CinemaPlayer] #mainVideo element not ready yet. Retrying in 100ms...');
      setTimeout(() => this.loadSource(url), 100);
      return;
    }

    this.showSpinner('Connecting to direct video source...');

    // Save current time if video was playing
    try {
      if (this.video && this.video.currentTime > 5) {
        this.savedPlaybackTime = this.video.currentTime;
      }
    } catch (e) {}

    console.log('[CinemaPlayer] Direct Source Playback (No Proxy):', url);

    this.video.src = url;
    this.video.load();
    this.video.play().catch(e => console.log('[CinemaPlayer] Auto-play pending user gesture:', e.message));
  }

  // Set & configure driver mirror servers
  setServers(serverList) {
    this.servers = serverList || [];
    this.currentServerIndex = 0;
    this.renderServerSelector();

    // Auto load primary stream from Server 1
    if (this.servers.length > 0) {
      const primary = this.servers.find(s => s.type === 'stream') || this.servers[0];
      this.loadSource(primary.url);
    }
  }

  // Switch to specific driver mirror
  switchServer(index) {
    if (!this.servers[index]) return;
    this.currentServerIndex = index;
    const selected = this.servers[index];
    console.log(`[CinemaPlayer] Switched to ${selected.name}`);

    this.updateActiveServerPill(index);

    if (selected.url.includes('http')) {
      if (selected.type === 'stream' || selected.id === 'r2' || selected.id === 'google') {
        this.loadSource(selected.url);
      } else {
        // External high-speed server (Hubcloud / Pixeldrain / Gofile)
        window.open(selected.url, '_blank');
      }
    }
  }

  // Set & configure movie qualities
  setQualities(qualitiesList, onSelectCallback) {
    this.qualities = qualitiesList || [];
    this.renderQualityMenu(onSelectCallback);
  }

  // Switch quality smoothly
  switchQuality(index, nexdriveUrl, label, callback) {
    this.currentQualityIndex = index;
    if (this.video && this.video.currentTime > 0) {
      this.savedPlaybackTime = this.video.currentTime;
    }
    const badge = document.getElementById('playerActiveQualityBadge');
    if (badge) badge.innerText = label;

    if (callback) callback(nexdriveUrl, label);
  }

  // Set & configure audio tracks
  setAudioTracks(tracks) {
    if (tracks && tracks.length > 0) {
      this.audioTracks = tracks;
    } else {
      this.audioTracks = ['Hindi [ORG / Main]', 'English [Dual Audio]'];
    }
    this.currentAudioIndex = 0;
    this.renderAudioMenu();
    this.renderAudioPills();

    const activeTrackName = this.audioTracks[0] || 'Audio';
    const label = document.getElementById('activeAudioLabel');
    if (label) label.innerText = activeTrackName.split(' ')[0];
  }

  switchAudio(index) {
    if (!this.audioTracks[index]) return;
    this.currentAudioIndex = index;
    const trackName = this.audioTracks[index];
    console.log(`[CinemaPlayer] Switched audio track to [${index}]: ${trackName}`);

    const label = document.getElementById('activeAudioLabel');
    if (label) label.innerText = trackName.split(' ')[0];

    // Check if HTML5 audio tracks API is supported natively
    if (this.video && this.video.audioTracks && this.video.audioTracks.length > 0) {
      for (let i = 0; i < this.video.audioTracks.length; i++) {
        this.video.audioTracks[i].enabled = (i === index);
      }
    }

    this.renderAudioMenu();
    this.renderAudioPills();
    this.closeAllMenus();
  }

  toggleAudioMenu(e) {
    if (e) e.stopPropagation();
    const dropdown = document.getElementById('audioMenuDropdown') || document.getElementById('audioMenuPopup');
    if (!dropdown) return;

    const isHidden = dropdown.classList.contains('hidden');
    this.closeAllMenus();
    if (isHidden) {
      this.renderAudioMenu();
      dropdown.classList.remove('hidden');
    }
  }

  toggleSpeedMenu(e) {
    if (e) e.stopPropagation();
    const dropdown = document.getElementById('speedMenuDropdown') || document.getElementById('speedMenuPopup');
    if (!dropdown) return;

    const isHidden = dropdown.classList.contains('hidden');
    this.closeAllMenus();
    if (isHidden) {
      this.renderSpeedMenu();
      dropdown.classList.remove('hidden');
    }
  }

  renderAudioMenu() {
    const menu = document.getElementById('audioMenuOptions');
    if (!menu) return;

    menu.innerHTML = this.audioTracks.map((t, idx) => {
      const isDefault = idx === this.currentAudioIndex;
      return `
        <button 
          onclick="player.switchAudio(${idx})" 
          class="w-full text-left px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-between transition-colors ${
            isDefault ? 'bg-brand-emerald text-black font-bold' : 'text-gray-300 hover:bg-white/10 hover:text-white'
          }"
        >
          <span>${t}</span>
          ${isDefault ? '<i data-lucide="check" class="w-3.5 h-3.5"></i>' : ''}
        </button>
      `;
    }).join('');

    if (window.lucide) lucide.createIcons();
  }

  renderAudioPills() {
    const container = document.getElementById('audioPillsContainer');
    if (!container) return;

    container.innerHTML = this.audioTracks.map((t, idx) => {
      const isDefault = idx === this.currentAudioIndex;
      return `
        <button 
          onclick="player.switchAudio(${idx})" 
          class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 flex-shrink-0 ${
            isDefault 
              ? 'bg-brand-emerald text-black font-black shadow-lg shadow-brand-emerald/20 border border-brand-emerald ring-2 ring-brand-emerald/40' 
              : 'bg-brand-surface text-gray-300 hover:text-white border border-brand-border hover:border-gray-500'
          }"
        >
          <i data-lucide="volume-2" class="w-3.5 h-3.5"></i>
          <span>${t}</span>
        </button>
      `;
    }).join('');

    if (window.lucide) lucide.createIcons();
  }

  renderSpeedMenu() {
    const menu = document.getElementById('speedMenuOptions');
    if (!menu) return;

    const speeds = [0.5, 0.75, 1, 1.25, 1.5, 2];
    const currentRate = this.video ? this.video.playbackRate : 1;

    menu.innerHTML = speeds.map(s => {
      const isCurrent = Math.abs(currentRate - s) < 0.01;
      return `
        <button 
          onclick="player.setSpeed(${s})" 
          class="w-full text-left px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center justify-between transition-colors ${
            isCurrent ? 'bg-brand-red text-white font-bold' : 'text-gray-300 hover:bg-white/10 hover:text-white'
          }"
        >
          <span>${s}x</span>
          ${isCurrent ? '<i data-lucide="check" class="w-3.5 h-3.5"></i>' : ''}
        </button>
      `;
    }).join('');

    if (window.lucide) lucide.createIcons();
  }

  // Playback Control Methods
  togglePlay() {
    if (this.video.paused || this.video.ended) {
      this.video.play().catch(e => console.warn('Play error:', e.message));
    } else {
      this.video.pause();
    }
  }

  skip(seconds) {
    if (this.video.duration) {
      const target = Math.max(0, Math.min(this.video.duration, this.video.currentTime + seconds));
      console.log(`[CinemaPlayer] Skipping ${seconds}s -> ${this.formatTime(target)}`);
      this.video.currentTime = target;
    }
  }

  toggleMute() {
    this.video.muted = !this.video.muted;
    this.updateVolumeUI();
  }

  setSpeed(rate) {
    this.video.playbackRate = rate;
    const speedLabel = document.getElementById('activeSpeedLabel');
    if (speedLabel) speedLabel.innerText = `${rate}x`;
    this.closeAllMenus();
  }

  toggleFullscreen() {
    const fsIcon = document.getElementById('fullscreenIcon');
    if (!document.fullscreenElement) {
      this.container.requestFullscreen().catch(e => console.warn(e));
      if (fsIcon) fsIcon.setAttribute('data-lucide', 'minimize');
    } else {
      document.exitFullscreen();
      if (fsIcon) fsIcon.setAttribute('data-lucide', 'maximize');
    }
    if (window.lucide) lucide.createIcons();
  }

  togglePiP() {
    if (document.pictureInPictureElement) {
      document.exitPictureInPicture();
    } else if (document.pictureInPictureEnabled) {
      this.video.requestPictureInPicture().catch(e => console.warn(e));
    }
  }

  // UI Renderers & Helpers
  renderServerSelector() {
    const container = document.getElementById('serverPillsContainer');
    if (!container) return;

    if (this.servers.length === 0) {
      container.innerHTML = `<span class="text-xs text-gray-500">No servers detected</span>`;
      return;
    }

    container.innerHTML = this.servers.map((s, idx) => {
      const isActive = idx === this.currentServerIndex;
      const activeClass = isActive
        ? 'bg-brand-red text-white font-bold border-brand-red shadow-md'
        : 'bg-[#12141a] text-gray-300 hover:text-white border-brand-border';

      return `
        <button onclick="player.switchServer(${idx})" id="serverBtn_${idx}" class="px-3 py-1.5 rounded-xl text-xs border ${activeClass} transition-all flex items-center gap-1.5 flex-shrink-0">
          <span class="w-1.5 h-1.5 rounded-full ${isActive ? 'bg-white' : 'bg-brand-emerald'}"></span>
          <span>${s.name}</span>
          ${s.badge ? `<span class="text-[9px] uppercase px-1 rounded bg-black/40 text-gray-300">${s.badge}</span>` : ''}
        </button>
      `;
    }).join('');

    if (window.lucide) lucide.createIcons();
  }

  updateActiveServerPill(activeIdx) {
    this.servers.forEach((_, idx) => {
      const btn = document.getElementById(`serverBtn_${idx}`);
      if (btn) {
        if (idx === activeIdx) {
          btn.className = 'px-3 py-1.5 rounded-xl text-xs border bg-brand-red text-white font-bold border-brand-red shadow-md transition-all flex items-center gap-1.5 flex-shrink-0';
        } else {
          btn.className = 'px-3 py-1.5 rounded-xl text-xs border bg-[#12141a] text-gray-300 hover:text-white border-brand-border transition-all flex items-center gap-1.5 flex-shrink-0';
        }
      }
    });
  }

  renderQualityMenu(onSelectCallback) {
    const menu = document.getElementById('qualityMenuOptions');
    if (!menu) return;

    menu.innerHTML = this.qualities.map((q, idx) => {
      return `
        <button onclick="player.switchQuality(${idx}, '${q.nexdriveUrl}', '${q.quality}', ${onSelectCallback})" class="cinema-menu-item">
          <span>${q.quality}</span>
          <span class="text-[10px] text-gray-400">${q.size || ''}</span>
        </button>
      `;
    }).join('');
  }

  renderAudioMenu() {
    const menu = document.getElementById('audioMenuOptions');
    if (!menu) return;

    menu.innerHTML = this.audioTracks.map((t, idx) => {
      const isDefault = idx === this.currentAudioIndex;
      return `
        <button onclick="player.switchAudio(${idx})" class="cinema-menu-item ${isDefault ? 'active' : ''}">
          ${t}
        </button>
      `;
    }).join('');
  }

  updatePlayState(isPlaying) {
    const centerBtn = document.getElementById('centerPlayBtn');
    const centerIcon = document.getElementById('centerPlayIcon');
    const controlIcon = document.getElementById('controlPlayIcon');

    if (isPlaying) {
      if (centerBtn) centerBtn.classList.add('opacity-0');
      if (centerIcon) centerIcon.setAttribute('data-lucide', 'pause');
      if (controlIcon) controlIcon.setAttribute('data-lucide', 'pause');
    } else {
      if (centerBtn) centerBtn.classList.remove('opacity-0');
      if (centerIcon) centerIcon.setAttribute('data-lucide', 'play');
      if (controlIcon) controlIcon.setAttribute('data-lucide', 'play');
    }
    if (window.lucide) lucide.createIcons();
  }

  updateTimelineProgress(pct, currentTime) {
    const slider = document.getElementById('timelineSlider');
    const progressBar = document.getElementById('playerProgressBar');
    const currDisplay = document.getElementById('currentTimeDisplay');

    if (slider) slider.value = pct;
    if (progressBar) progressBar.style.width = `${pct}%`;
    if (currDisplay) currDisplay.innerText = this.formatTime(currentTime);
  }

  updateDurationDisplay() {
    const durDisplay = document.getElementById('durationDisplay');
    if (durDisplay && this.video.duration) {
      durDisplay.innerText = this.formatTime(this.video.duration);
    }
  }

  updateVolumeUI() {
    const v = this.video;
    const volIcon = document.getElementById('volIcon');
    const volSlider = document.getElementById('volSlider');

    if (volSlider) volSlider.value = v.muted ? 0 : v.volume;

    if (volIcon) {
      if (v.muted || v.volume === 0) {
        volIcon.setAttribute('data-lucide', 'volume-x');
      } else if (v.volume < 0.5) {
        volIcon.setAttribute('data-lucide', 'volume-1');
      } else {
        volIcon.setAttribute('data-lucide', 'volume-2');
      }
      if (window.lucide) lucide.createIcons();
    }
  }

  showControls() {
    const topBar = document.getElementById('playerTopBar');
    const bottomBar = document.getElementById('playerControlsBar');
    if (topBar) topBar.classList.remove('opacity-0');
    if (bottomBar) bottomBar.classList.remove('opacity-0');
  }

  hideControls() {
    const topBar = document.getElementById('playerTopBar');
    const bottomBar = document.getElementById('playerControlsBar');
    if (topBar) topBar.classList.add('opacity-0');
    if (bottomBar) bottomBar.classList.add('opacity-0');
    this.closeAllMenus();
  }

  resetControlsTimeout() {
    clearTimeout(this.hideControlsTimeout);
    this.hideControlsTimeout = setTimeout(() => {
      if (!this.video.paused) this.hideControls();
    }, 3000);
  }

  closeAllMenus() {
    ['audioMenuDropdown', 'speedMenuDropdown', 'qualityMenuDropdown', 'audioMenuPopup', 'qualityMenuPopup', 'speedMenuPopup'].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.classList.add('hidden');
    });
  }

  showSpinner(text) {
    const overlay = document.getElementById('playerLoading');
    const textEl = document.getElementById('playerLoadingText');
    if (textEl && text) textEl.innerText = text;
    if (overlay) overlay.classList.remove('hidden');
  }

  hideSpinner() {
    const overlay = document.getElementById('playerLoading');
    if (overlay) overlay.classList.add('hidden');
  }

  handleStreamError() {
    this.hideSpinner();
    console.warn('[CinemaPlayer] Stream error occurred. Checking next server...');
    // If current server failed and there are more servers, try next server
    if (this.currentServerIndex + 1 < this.servers.length) {
      const nextIdx = this.currentServerIndex + 1;
      alert(`Current stream server encountered an issue. Switching to ${this.servers[nextIdx].name}...`);
      this.switchServer(nextIdx);
    } else {
      alert('Could not stream video on current server. Please select another Driver Server from the list.');
    }
  }

  handleKeyboard(e) {
    if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
    const playerSection = document.getElementById('playerSection');
    if (playerSection && playerSection.classList.contains('hidden')) return;

    if (e.key === ' ' || e.key === 'k') {
      e.preventDefault();
      this.togglePlay();
    } else if (e.key === 'ArrowLeft' || e.key === 'j') {
      e.preventDefault();
      this.skip(-10);
    } else if (e.key === 'ArrowRight' || e.key === 'l') {
      e.preventDefault();
      this.skip(10);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      this.video.volume = Math.min(1, this.video.volume + 0.1);
      this.updateVolumeUI();
    } else if (e.key === 'ArrowDown') {
      e.preventDefault();
      this.video.volume = Math.max(0, this.video.volume - 0.1);
      this.updateVolumeUI();
    } else if (e.key === 'f') {
      e.preventDefault();
      this.toggleFullscreen();
    } else if (e.key === 'm') {
      e.preventDefault();
      this.toggleMute();
    }
  }

  formatTime(seconds) {
    if (isNaN(seconds) || seconds < 0) return '00:00:00';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    const pad = (n) => String(n).padStart(2, '0');
    return h > 0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `00:${pad(m)}:${pad(s)}`;
  }
}

// Global player instance
window.player = new CinemaPlayer();

// Auto-initialize as soon as DOM is ready
if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      window.player.ensureInitialized();
    });
  } else {
    window.player.ensureInitialized();
  }
}
