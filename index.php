<?php
/**
 * Modern Laragon Dashboard v10 (LuissXD Refined)
 * Theme: Cyberpunk Red Ultimate
 * Changes: Removed Design section, Restored Bold Gradient Logo
 */

require_once __DIR__ . '/src/ProjectSorter.php';

/**
 * Escapes a value for safe output in an HTML context (text or attribute).
 * Project names come from real directory names on disk, which are not
 * necessarily trusted (any local process/project can create a folder with
 * an arbitrary name), so every dynamic value must be escaped before being
 * echoed into the page to prevent stored XSS.
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Directories to ignore
$ignore = array('.', '..', '.git', '.idea', 'vscode', 'node_modules', 'vendor');
// glob() returns false (not an empty array) if the current directory can't
// be read at all, e.g. a permissions issue; fall back to an empty list
// instead of feeding false into array_filter()/is_dir().
$dirs = array_filter(glob('*') ?: [], 'is_dir');
$projects = [];

foreach ($dirs as $dir) {
    if (in_array($dir, $ignore, true)) {
        continue;
    }

    // A project folder can be deleted, renamed, or made unreadable by
    // another process between the glob() call above and this line
    // (classic TOCTOU race), or simply be a permission-denied mount.
    // filemtime() returns false and raises an E_WARNING in that case;
    // suppress the warning and just skip the entry instead of letting
    // it leak a stat-failed warning into the page or, since PHP 8,
    // pass a non-numeric false into date() (deprecated, and would
    // render as "Jan 01" instead of being omitted).
    $timestamp = @filemtime($dir);
    if ($timestamp === false) {
        continue;
    }

    $projects[] = [
        'name' => $dir,
        'timestamp' => $timestamp,
        'date' => date("M d", $timestamp),
    ];
}

$projects = ProjectSorter::byMostRecentlyModified($projects);

// Sidebar Menus (Design Removed)
$menuGroups = [
    'Quick Access' => [
        ['name' => 'phpMyAdmin', 'url' => '/phpmyadmin', 'icon' => 'fa-database'],
        ['name' => 'Terminal', 'url' => '#', 'icon' => 'fa-terminal'],
        ['name' => 'Virtual Host', 'url' => '#', 'icon' => 'fa-network-wired'],
    ],
    'Development' => [
        ['name' => 'GitHub', 'url' => 'https://github.com', 'icon' => 'fa-brands fa-github'],
        ['name' => 'StackOverflow', 'url' => 'https://stackoverflow.com', 'icon' => 'fa-brands fa-stack-overflow'],
        ['name' => 'ChatGPT', 'url' => 'https://chat.openai.com', 'icon' => 'fa-solid fa-robot'],
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LuissxD Dashboard</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;900&family=JetBrains+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #020000;
            --primary-red: #ff001c;
            --dark-red: #4a0007;
            --surface: rgba(20, 0, 0, 0.6);
            --surface-border: rgba(255, 0, 0, 0.4);
            --text-main: #ffffff;
            --text-dim: #a0a0a0;
            --font-main: 'Outfit', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --sidebar-width: 280px;
            --header-height: 80px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-deep);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-red);
            border-radius: 3px;
        }

        body {
            background-color: var(--bg-deep);
            color: var(--text-main);
            font-family: var(--font-main);
            height: 100vh;
            overflow: hidden;
            display: flex;
        }

        /* BACKGROUND */
        .bg-grid {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-image:
                linear-gradient(rgba(255, 0, 28, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 0, 28, 0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: -1;
            perspective: 1000px;
            transform-style: preserve-3d;
            animation: moveGrid 20s linear infinite;
        }

        @keyframes moveGrid {
            0% {
                transform: translateY(0);
            }

            100% {
                transform: translateY(50px);
            }
        }

        .ambient-glow {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80vw;
            height: 80vh;
            background: radial-gradient(circle, rgba(74, 0, 7, 0.4) 0%, transparent 70%);
            z-index: -1;
            pointer-events: none;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            background: rgba(5, 0, 0, 0.95);
            border-right: 1px solid var(--surface-border);
            display: flex;
            flex-direction: column;
            padding: 2rem;
            z-index: 100;
            backdrop-filter: blur(15px);
            transition: transform 0.3s ease, width 0.3s ease;
            flex-shrink: 0;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar.collapsed {
            width: 80px;
            padding: 2rem 10px;
        }

        /* LOGO - Animated Premium Style */
        .logo {
            font-size: 3rem;
            font-weight: 900;
            letter-spacing: -2px;
            line-height: 1;
            margin-bottom: 2rem;
            background: linear-gradient(to right, #fff 20%, var(--primary-red) 50%, #fff 80%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shine 4s linear infinite;
            font-family: 'Outfit', sans-serif;
            text-transform: uppercase;
        }

        @keyframes shine {
            to {
                background-position: 200% center;
            }
        }

        .sidebar.collapsed .logo {
            font-size: 1.5rem;
            letter-spacing: -1px;
            margin-bottom: 2rem;
        }

        .stats-row {
            display: flex;
            gap: 10px;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(145deg, rgba(30, 0, 0, 0.6), rgba(10, 0, 0, 0.8));
            border: 1px solid var(--surface-border);
            border-radius: 8px;
            text-align: center;
            flex: 1;
            padding: 12px 5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            border-color: var(--primary-red);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 0, 28, 0.15);
        }

        .stat-num {
            font-size: 1.6rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
        }

        .stat-label {
            font-size: 0.65rem;
            color: var(--text-dim);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .sidebar.collapsed .stats-row {
            display: none;
        }

        .menu-group {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 0.7rem;
            color: var(--primary-red);
            margin-bottom: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            padding-left: 10px;
        }

        .sidebar.collapsed .section-title {
            display: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            margin-bottom: 5px;
            color: var(--text-dim);
            text-decoration: none;
            border-radius: 8px;
            transition: 0.2s;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .nav-link:hover {
            background: rgba(255, 0, 28, 0.1);
            color: #fff;
            border-color: var(--primary-red);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            color: var(--primary-red);
            font-size: 1rem;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 15px 0;
        }

        .refresh-btn {
            margin-top: auto;
            cursor: pointer;
            background: var(--primary-red);
            color: #fff;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            font-weight: 700;
            transition: 0.3s;
            border: none;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .refresh-btn:hover {
            background: #cc0016;
            box-shadow: 0 0 15px rgba(255, 0, 28, 0.4);
        }

        .sidebar.collapsed .refresh-btn span {
            display: none;
        }

        .clock-display {
            margin-top: 1rem;
            text-align: center;
            font-family: var(--font-mono);
            font-weight: 700;
            color: #fff;
            background: #000;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--surface-border);
        }

        .sidebar.collapsed .clock-display {
            font-size: 0.7rem;
            padding: 5px;
        }

        /* MAIN CONTENT */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .toolbar {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            background: rgba(0, 0, 0, 0.3);
            border-bottom: 2px solid var(--primary-red);
            backdrop-filter: blur(5px);
            flex-shrink: 0;
        }

        .toggle-menu {
            color: var(--text-dim);
            font-size: 1.2rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: 0.2s;
        }

        .toggle-menu:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .toolbar-center {
            flex-grow: 1;
            max-width: 600px;
            margin: 0 2rem;
            position: relative;
        }

        .search-bar {
            width: 100%;
            background: #000;
            border: 1px solid var(--surface-border);
            padding: 10px 45px 10px 15px;
            color: #fff;
            border-radius: 6px;
            font-family: var(--font-main);
        }

        .search-bar:focus {
            border-color: var(--primary-red);
            outline: none;
            box-shadow: 0 0 15px rgba(255, 0, 28, 0.2);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .tool-btn {
            background: #000;
            border: 1px solid var(--surface-border);
            color: var(--text-dim);
            width: 38px;
            height: 38px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .tool-btn:hover,
        .tool-btn.active {
            border-color: var(--primary-red);
            color: var(--primary-red);
            box-shadow: 0 0 10px rgba(255, 0, 28, 0.2);
        }

        /* GRID */
        .grid-container {
            padding: 2rem;
            overflow-y: auto;
            flex-grow: 1;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .grid.compact {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .card {
            background: linear-gradient(145deg, rgba(20, 0, 0, 0.8), rgba(10, 0, 0, 0.9));
            border: 1px solid var(--surface-border);
            padding: 1.5rem;
            border-radius: 8px;
            position: relative;
            text-decoration: none;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 160px;
            transition: all 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-red);
            box-shadow: 0 10px 40px rgba(255, 0, 28, 0.1);
        }

        .card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--primary-red);
            box-shadow: 0 0 10px var(--primary-red);
        }

        .card-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .card-date {
            font-size: 0.8rem;
            color: var(--text-dim);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-led {
            width: 6px;
            height: 6px;
            background: #00ff88;
            border-radius: 50%;
            box-shadow: 0 0 5px #00ff88;
        }

        .card-actions {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .local-link {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: #666;
            transition: 0.3s;
        }

        .card:hover .local-link {
            color: var(--primary-red);
        }

        .pin-icon {
            opacity: 0.2;
            transition: 0.2s;
            cursor: pointer;
            color: #fff;
        }

        .pin-icon:hover,
        .pinned .pin-icon {
            opacity: 1;
            color: var(--primary-red);
            text-shadow: 0 0 10px var(--primary-red);
        }

        .project-item.pinned .card {
            background: rgba(40, 0, 0, 0.6);
            border-color: rgba(255, 0, 0, 0.3);
        }

        .bg-lg-text {
            position: absolute;
            right: -10px;
            bottom: -20px;
            font-size: 6rem;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.03);
            pointer-events: none;
            transition: 0.3s;
        }

        .card:hover .bg-lg-text {
            transform: scale(1.1) rotate(-10deg);
            color: rgba(255, 0, 28, 0.1);
        }

        @media (max-width: 900px) {
            .sidebar {
                position: absolute;
                height: 100%;
                transform: translateX(-100%);
                width: 250px;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .toggle-menu {
                display: flex;
            }
        }

        @media (min-width: 901px) {
            .toggle-menu {
                display: flex;
            }
        }
    </style>
</head>

<body>
    <div class="bg-grid"></div>
    <div class="ambient-glow"></div>

    <aside class="sidebar" id="sidebar">
        <!-- Brand Restored -->
        <!-- Brand Restored -->
        <div class="logo">LuissxD</div>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-num"><?= count($projects) ?></div>
                <div class="stat-label">Projects</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= phpversion() ?></div>
                <div class="stat-label">PHP</div>
            </div>
        </div>

        <?php foreach ($menuGroups as $groupName => $links): ?>
            <div class="menu-group">
                <div class="section-title"><?= e($groupName) ?></div>
                <nav>
                    <?php foreach ($links as $link): ?>
                        <a href="<?= e($link['url']) ?>" target="_blank" class="nav-link">
                            <i class="fas <?= e($link['icon']) ?>"></i>
                            <span><?= e($link['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            </div>
        <?php endforeach; ?>

        <button class="refresh-btn" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> <span>RELOAD</span>
        </button>


    </aside>

    <main class="main-content">
        <header class="toolbar">
            <div class="toggle-menu" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </div>
            <div class="toolbar-center">
                <input type="text" id="searchInput" class="search-bar" placeholder="Search ( Press / )">
                <i class="fas fa-search search-icon"></i>
            </div>
            <div class="toolbar-actions">
                <button class="tool-btn" onclick="toggleSort()"><i class="fas fa-sort"></i></button>
                <button class="tool-btn active" id="btn-grid" onclick="setView('grid')"><i
                        class="fas fa-th-large"></i></button>
                <button class="tool-btn" id="btn-compact" onclick="setView('compact')"><i
                        class="fas fa-th"></i></button>
                <button class="tool-btn" onclick="toggleFullScreen()"><i class="fas fa-expand"></i></button>
            </div>
        </header>

        <div class="grid-container">
            <div class="grid" id="grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-item" data-name="<?= e(strtolower($project['name'])) ?>">
                        <a href="/<?= e(rawurlencode($project['name'])) ?>" class="card">
                            <div class="bg-lg-text"><?= e(strtoupper(substr($project['name'], 0, 2))) ?></div>
                            <div>
                                <div style="display:flex; justify-content:space-between;">
                                    <div class="card-name"><?= e($project['name']) ?></div>
                                    <i class="fas fa-thumbtack pin-icon" data-project-name="<?= e($project['name']) ?>"
                                        onclick="event.preventDefault(); togglePin(this)"></i>
                                </div>
                                <div class="card-date"><span class="status-led"></span> <?= e($project['date']) ?></div>
                            </div>
                            <div class="card-actions">
                                <span class="local-link">localhost/<?= e($project['name']) ?></span>
                                <i class="fas fa-external-link-alt" style="color:var(--primary-red); font-size:0.8rem;"></i>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const grid = document.getElementById('grid');
        function toggleSidebar() { sidebar.classList.toggle('collapsed'); sidebar.classList.toggle('active'); }



        function setView(mode) {
            if (mode === 'compact') {
                grid.classList.add('compact');
                document.getElementById('btn-compact').classList.add('active');
                document.getElementById('btn-grid').classList.remove('active');
            } else {
                grid.classList.remove('compact');
                document.getElementById('btn-grid').classList.add('active');
                document.getElementById('btn-compact').classList.remove('active');
            }
        }

        function toggleFullScreen() {
            !document.fullscreenElement ? document.documentElement.requestFullscreen() : document.exitFullscreen && document.exitFullscreen();
        }

        let pinned = JSON.parse(localStorage.getItem('my_pinned') || '[]');
        let sortMode = 'date';

        function togglePin(icon) {
            const name = icon.dataset.projectName;
            const index = pinned.indexOf(name);
            index > -1 ? pinned.splice(index, 1) : pinned.push(name);
            localStorage.setItem('my_pinned', JSON.stringify(pinned));
            render();
        }

        function toggleSort() { sortMode = sortMode === 'date' ? 'name' : 'date'; render(); }

        function render() {
            const items = Array.from(document.querySelectorAll('.project-item'));
            items.forEach(item => {
                const name = item.querySelector('.pin-icon').dataset.projectName;
                pinned.includes(name) ? item.classList.add('pinned') : item.classList.remove('pinned');
            });

            items.sort((a, b) => {
                const aPinned = a.classList.contains('pinned');
                const bPinned = b.classList.contains('pinned');
                if (aPinned && !bPinned) return -1;
                if (!aPinned && bPinned) return 1;
                return sortMode === 'date' ? 0 : a.dataset.name.localeCompare(b.dataset.name);
            });

            const container = document.getElementById('grid');
            container.innerHTML = '';
            items.forEach(i => container.appendChild(i));
        }
        render();

        document.getElementById('searchInput').addEventListener('input', (e) => {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.project-item').forEach(el => el.style.display = el.dataset.name.includes(val) ? 'block' : 'none');
        });
        document.addEventListener('keydown', e => {
            if (e.key === '/' && document.activeElement.id !== 'searchInput') {
                e.preventDefault(); document.getElementById('searchInput').focus();
            }
        });
    </script>
</body>

</html>