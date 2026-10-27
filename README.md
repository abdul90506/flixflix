# FLIXFLIX - Streaming & Movie Scraper Engine (Powered by VegaMovies)

A high-performance streaming web application built with **Pure PHP, HTML5, CSS3, and Vanilla JavaScript** that integrates **TheMovieDB (TMDB)** for live movie discovery and automatically resolves direct stream & driver download links from **VegaMovies (`https://new1.vegamovies.lease/`)** with quality selection, multi-audio support, and web series episode handling.

## Features

1. **Pure PHP Stack (No Node.js or npm Required)**:
   - Built for standard Apache/Nginx web servers and XAMPP/WAMP environments.
   - Zero external package managers, zero `node_modules` overhead.
   - Unified API in `api.php` with cURL handling TMDB and VegaMovies scraping.

2. **VegaMovies Engine (100% Real Links & Drivers)**:
   - Real-time search via VegaMovies Typesense API (`ts-search.php`).
   - Direct VegaMovies URL parsing for both **Movies** and **Web Series with Seasons and Episodes**.
   - Direct stream extractors:
     * **Cloudflare R2** (`r2.cloudflarestorage.com`) — *Direct Stream + Seek Support (Fastest CDN)*
     * **Google UserContent** (`video-downloads.googleusercontent.com`) — *Instant Google CDN Direct Stream*
     * **HubCloud 10Gbps Server** (`gpdl2.hubcloud.ist`) — *10Gbps Speed Download*
     * **Pixeldrain Server** (`pixeldrain.dev`) — *Direct Cloud Mirror*
     * **Gofile Server** (`gofile.io`) — *Max Speed Download*
     * **Filepress / Filebee** (`filebee.xyz`) — *Resumable G-Drive Mirror*

3. **Multi-Audio Track Auto-Detection & Switching**:
   - Detects all audio tracks in `.mkv` files (e.g. `Hindi ORG`, `English`, `Dual Audio`, `Tamil`, `Telugu`).
   - Dedicated Audio Track Selector Pills directly under the player.
   - In-player Audio switcher popup with active checkmarks.

4. **Dedicated Theater Cinema Watch Page (`watch.php`)**:
   - Full-width centered cinema player with custom scrubber timeline and buffer bar.
   - Season Selector Tabs & Episode Selector Grid for series.
   - Quality / Resolution Selector (`480p`, `720p`, `1080p`, `4K UHD`).
   - Drive Provider Selector (`⚡ V-Cloud`, `⚡ G-Direct`, `📁 Filepress`).
   - Server Mirror Selector (`Cloudflare R2`, `Google UserContent`, `HubCloud 10Gbps`, `Pixeldrain`, `Gofile`).
   - 1-Click Direct Download Center & Copy Link buttons.

## Project Structure

```
flixflix/
├── api.php             # Unified PHP Backend API (TMDB, Vega Scraper, Resolvers)
├── index.php           # Home Catalog & Search Page
├── watch.php           # Dedicated Theater Cinema Watch Page
├── .htaccess           # Apache rewrite rules for clean routing
├── css/
│   └── player.css      # Custom Cinema Video Player styles
├── js/
│   └── player.js       # Standalone Cinema Video Player Engine
└── README.md
```

## How to Run

1. Place the `flixflix` folder inside your web server root (e.g., `c:\xamppnew\htdocs\flixflix` in XAMPP).
2. Start **Apache** in XAMPP Control Panel.
3. Open your browser:
   * **Home Catalog:** `http://localhost/flixflix/`
   * **Cinema Watch Page:** `http://localhost/flixflix/watch.php`
