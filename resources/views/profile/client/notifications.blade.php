<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">

<x-marketplace-layout title="Mes notifications — Souq">

    <div class="souq-app compte-layout">
        @include('profile.client.partials.sidebar', ['active' => 'notifications'])

        <main>
            <h1 class="page-title">Mes notifications</h1>
            <div class="page-sub">Décisions de l'administration et activité récente sur ton compte</div>

            <div class="panel" style="padding: 0; margin-top: 20px;">
                @forelse ($notifications as $notification)
                    <div class="moderation-card" style="align-items: center; {{ ! $notification->lu ? 'background: rgba(30,92,99,0.04);' : '' }}">
                        <div style="width:40px; height:40px; border-radius:50%; background: var(--paper); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="ti {{ $notification->icone() }}" style="font-size:20px; color: {{ in_array($notification->type, ['compte_bloque', 'drone_erreur']) ? 'var(--terracotta)' : '#1E6B3A' }};"></i>
                        </div>
                        <div class="moderation-info">
                            <div class="nom" style="font-size:14px;">{{ $notification->message }}</div>
                            <div class="meta">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="ti ti-bell-off"></i>
                        Aucune notification pour le moment.
                    </div>
                @endforelse
            </div>
        </main>
    </div>

</x-marketplace-layout>
