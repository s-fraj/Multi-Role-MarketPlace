@php
    $sidebarUser = Auth::user();
    $sidebarParts = collect(preg_split('/\s+/', trim($sidebarUser->name)))->filter();
    $sidebarInitials = $sidebarParts->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->take(2)->implode('');
    $sidebarNotifNonLues = $sidebarUser->notificationsClient()->where('lu', false)->count();
@endphp
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
<aside>
    <div class="account-card">
        <div class="avatar-wrap">
            <div class="avatar" data-avatar-display>
                @if ($sidebarUser->avatar)
                    <img src="{{ $sidebarUser->avatarUrl() }}" alt="">
                @else
                    {{ $sidebarInitials }}
                @endif
            </div>
            <label class="avatar-edit-btn" title="Changer la photo">
                <i class="ti ti-pencil"></i>
                <input type="file" id="sidebar-avatar-input" accept="image/png, image/jpeg, image/webp" hidden>
            </label>
        </div>
        <div class="account-name">{{ $sidebarUser->name }}</div>
        <div class="account-email">{{ $sidebarUser->email }}</div>
    </div>

    <nav class="side-nav">
        <a href="{{ route('compte') }}" @class(['active' => ($active ?? null) === 'compte'])><i class="ti ti-layout-dashboard"></i> Mon compte</a>
        <a href="{{ route('client.commandes') }}" @class(['active' => ($active ?? null) === 'commandes'])><i class="ti ti-package"></i> Mes commandes  </a>        
        <a href="{{ route('client.avis') }}" @class(['active' => ($active ?? null) === 'avis'])><i class="ti ti-star"></i> Mes avis</a>
        <a href="{{ route('favoris.index') }}" @class(['active' => ($active ?? null) === 'favoris'])><i class="ti ti-heart"></i> Favoris </a>
        <a href="{{ route('profile.edit') }}"><i class="ti ti-map-pin"></i> Adresses</a>
        <a href="{{ route('notifications.index') }}" @class(['active' => ($active ?? null) === 'notifications'])>
            <i class="ti ti-bell"></i> Notifications 
            @if ($sidebarNotifNonLues > 0)
                <span class="side-nav-badge">{{ $sidebarNotifNonLues }}</span>
            @endif
        </a>
        <a href="{{ route('profile.edit') }}" @class(['active' => ($active ?? null) === 'parametres'])><i class="ti ti-settings"></i> Paramètres</a>
    </nav>
</aside>

<script>
    (function () {
        var input = document.getElementById('sidebar-avatar-input');
        if (! input) return;

        input.addEventListener('change', function () {
            if (! input.files || ! input.files[0]) return;

            var formData = new FormData();
            formData.append('photo', input.files[0]);

            var csrfToken = window.souqCsrfToken || document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('profile.photo.update') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            })
                .then(async function (response) {
                    var data = await response.json().catch(function () { return {}; });
                    if (! response.ok) throw new Error(data.message || "Une erreur est survenue.");

                    document.querySelectorAll('[data-avatar-display]').forEach(function (el) {
                        el.innerHTML = '<img src="' + data.url + '" alt="">';
                    });

                    if (window.showToast) window.showToast(data.message, 'success');
                })
                .catch(function (err) {
                    if (window.showToast) window.showToast(err.message, 'error');
                    else alert(err.message);
                })
                .finally(function () {
                    input.value = '';
                });
        });
    })();
</script>
