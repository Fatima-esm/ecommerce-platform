<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ShopApp')</title>
    <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #1e293b;
            --sidebar-color: #94a3b8;
            --sidebar-active: #ffffff;
            --sidebar-hover: #334155;
            --primary-color: #6a994e;
        }
        body { margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; display: flex; min-height: 100vh; direction: ltr; text-align: left; }
        aside.sidebar { width: var(--sidebar-width); background-color: var(--sidebar-bg); color: var(--sidebar-color); position: fixed; top: 0; bottom: 0; left: 0; display: flex; flex-direction: column; padding: 20px; box-sizing: border-box; z-index: 100; }
        aside.sidebar .brand-area { font-size: 22px; font-weight: bold; color: #ffffff; margin-bottom: 35px; text-align: center; }
        aside.sidebar nav { display: flex; flex-direction: column; gap: 10px; flex-grow: 1; }
        aside.sidebar nav a { color: var(--sidebar-color); text-decoration: none; padding: 12px 15px; border-radius: 8px; transition: 0.3s; font-size: 15px; display: flex; align-items: center; gap: 10px; }
        aside.sidebar nav a:hover, aside.sidebar nav a.active { background-color: var(--sidebar-hover); color: var(--sidebar-active); }
        .main-wrapper { margin-left: var(--sidebar-width); flex-grow: 1; display: flex; flex-direction: column; min-width: 0; }
        header.topbar { background: #ffffff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); height: 70px; box-sizing: border-box; }
        header.topbar .brand { font-size: 20px; font-weight: bold; color: #1e293b; text-decoration: none; }
        .container { padding: 30px; max-width: 1400px; box-sizing: border-box; }
        .panel { background: #ffffff; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; }
        .notice { padding: 12px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; }
        .notice.hidden { display: none; }
        @media (max-width: 900px) { aside.sidebar { width: 70px; padding: 10px; } aside.sidebar .brand-area, aside.sidebar nav a span { display: none; } .main-wrapper { margin-left: 70px; } }
    </style>
    @stack('styles')
</head>
<body>

    <!-- القائمة الجانبية المشتركة (تعدل هنا مرة واحدة لتنعكس على الكل) -->
    <aside class="sidebar">
        <div class="brand-area" id="sidebarBrand">ShopApp</div>
        <nav id="sidebarNav">
            <a href="/products">📦 <span>Products</span></a>
            <a href="/my-orders">📋 <span>My Orders</span></a>
            <a href="/checkout">🛒 <span>Cart</span></a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <!-- الهيدر العلوي المشترك -->
        <header class="topbar">
            <a class="brand" href="/products" id="topbarBrand">ShopApp Store</a>
            <div style="display: flex; align-items: center; gap: 15px;">
                @yield('topbar-actions')
                <button class="btn secondary" id="logoutBtn" type="button" style="padding: 8px 15px; background: #fee2e2; color: #dc2626; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Logout
                </button>
            </div>
        </header>

        <!-- محتوى الصفحة المتغير -->
        <main class="container">
            @yield('content')
        </main>
    </div>

    <script src="{{ asset('assets/config.js') }}"></script>
    <script src="{{ asset('assets/api.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            if (typeof currentUser === 'function') {
                const user = currentUser();
                if (user?.role === 'admin') {
                    document.getElementById('sidebarBrand').textContent = 'Admin Panel';
                    document.getElementById('sidebarNav').innerHTML = `
                        <a href="/admin/products">📦 <span>Products</span></a>
                        <a href="/admin/orders">📋 <span>Customer Orders</span></a>
                        <a href="/admin/add">⚙️ <span>Add Product</span></a>
                    `;
                }
            }
            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn && typeof logout === 'function') {
                logoutBtn.addEventListener('click', logout);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>